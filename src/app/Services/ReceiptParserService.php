<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReceiptParserService
{
    /**
     * Parse single receipt image using 9router AI Vision API.
     */
    public function parseImage(string $filePath, string $priceType = 'auto'): array
    {
        return $this->parseSingleImage($filePath, $priceType);
    }

    /**
     * Parse multiple receipt images (overlapping screenshots) safely by batching calls to avoid API timeouts.
     */
    public function parseImages(array $filePaths, string $priceType = 'auto'): array
    {
        $validPaths = array_filter($filePaths, 'file_exists');
        if (empty($validPaths)) {
            throw new \InvalidArgumentException('Tidak ada file gambar struk yang valid di server.');
        }

        // If only 1 image, process directly
        if (count($validPaths) === 1) {
            return $this->parseSingleImage(reset($validPaths), $priceType);
        }

        // Process each screenshot image individually to prevent 9router 90s cURL payload timeout
        $allRawItems = [];
        $merchantName = 'Toko / Restoran';
        $maxDeliveryFee = 0;
        $maxServiceFee = 0;
        $maxDiscount = 0;
        $maxTotal = 0;
        $successfulParses = 0;
        $errors = [];

        foreach ($validPaths as $path) {
            $parsed = $this->parseSingleImage($path, $priceType);

            if ($parsed['success']) {
                $successfulParses++;
                if (!empty($parsed['merchant_name']) && $parsed['merchant_name'] !== 'Toko / Restoran') {
                    $merchantName = $parsed['merchant_name'];
                }
                foreach ($parsed['items'] as $item) {
                    $allRawItems[] = $item;
                }

                $maxDeliveryFee = max($maxDeliveryFee, $parsed['delivery_fee']);
                $maxServiceFee = max($maxServiceFee, $parsed['service_fee']);
                $maxDiscount = max($maxDiscount, $parsed['discount']);
                $maxTotal = max($maxTotal, $parsed['total']);
            } else {
                $errors[] = $parsed['error'] ?? 'Unknown error';
            }
        }

        if ($successfulParses === 0) {
            return $this->emptyFallback('Gagal memproses gambar-gambar struk: ' . implode(' | ', $errors));
        }

        // Deduplicate items extracted across multiple screenshot images
        $deduplicatedItems = $this->deduplicateItems($allRawItems);

        // Perform Price Format Enforcing or Auto-Crosscheck
        $processed = $this->applyPriceFormatRules($deduplicatedItems, $priceType, $maxDeliveryFee, $maxServiceFee, $maxDiscount, $maxTotal);

        return [
            'success' => true,
            'merchant_name' => $merchantName,
            'items' => $processed['items'],
            'delivery_fee' => $maxDeliveryFee,
            'service_fee' => $maxServiceFee,
            'discount' => $maxDiscount,
            'total' => $maxTotal,
            'auto_corrected' => $processed['auto_corrected'],
            'auto_corrected_message' => $processed['auto_corrected_message'],
        ];
    }

    /**
     * Internal method to parse a single receipt image with user-selected price format mode.
     */
    protected function parseSingleImage(string $filePath, string $priceType = 'auto'): array
    {
        $apiKey = config('services.ninerouter.api_key', env('NINEROUTER_API_KEY', ''));
        $baseUrl = config('services.ninerouter.api_base', env('NINEROUTER_API_BASE', 'https://api.9router.com/v1'));
        $model = config('services.ninerouter.model', env('NINEROUTER_MODEL', 'gemma4-31b'));

        if (!file_exists($filePath)) {
            return $this->emptyFallback('File struk tidak ditemukan di server: ' . $filePath);
        }

        $imageBytes = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageBytes);

        $priceInstruction = "";
        if ($priceType === 'unit_price') {
            $priceInstruction = "PETUNJUK USER (PENTING): Pengguna mengonfirmasi bahwa nominal yang tertera pada kolom harga di struk adalah HARGA SATUAN (Unit Price per 1 pcs). Ambil angka tersebut langsung sebagai field `price`.";
        } elseif ($priceType === 'total_price') {
            $priceInstruction = "PETUNJUK USER (PENTING): Pengguna mengonfirmasi bahwa nominal yang tertera pada kolom harga di struk adalah HARGA TOTAL KUANTITAS (Subtotal untuk `qty` barang tersebut). Kamu HARUS MEMBAGI nominal tersebut dengan `qty` (yaitu price_satuan = nominal / qty) agar field `price` pada JSON berisi HARGA SATUAN per 1 pcs.";
        } else {
            $priceInstruction = "EVALUASI OTOMATIS: Evaluasi apakah angka di kolom harga struk merupakan harga satuan atau harga total kuantitas. Field `price` pada JSON HARUS SELALU BERISI HARGA SATUAN per 1 pcs. Jika di struk tertulis 2x dan kolom harga 10.000 (total untuk 2 pcs), bagilah dengan 2 sehingga `price` = 5000.";
        }

        $prompt = <<<PROMPT
Anda adalah asisten AI ekstraksi struk/nota belanja di Indonesia (ShopeeFood, GoFood, GrabFood, Restoran, Supermarket, dll).
Tugas Anda adalah membaca gambar struk ini dan mengembalikan JSON HANYA dengan struktur berikut tanpa teks pembuka atau penutup:

{
  "merchant_name": "Nama Toko / Restoran",
  "items": [
    {
      "name": "Nama Item 1",
      "qty": 1,
      "price": 25000
    }
  ],
  "delivery_fee": 10000,
  "service_fee": 3000,
  "discount": 5000,
  "total": 33000
}

ATURAN DEDUKSI HARGA:
{$priceInstruction}

Aturan Tambahan:
1. Field `price` adalah HARGA SATUAN (Unit Price per 1 pcs) angka bulat tanpa desimal atau titik/koma dalam Rupiah.
2. `delivery_fee` adalah biaya ongkos kirim (0 jika tidak ada).
3. `service_fee` adalah total biaya layanan, biaya penanganan, biaya aplikasi, atau pembulatan.
4. `discount` adalah total potongan harga / promo / voucher (0 jika tidak ada).
5. Jangan sertakan item promo/diskon ke dalam list `items`, diskon masukkan ke field `discount`.
6. Kembalikan format JSON murni tanpa markdown.
PROMPT;

        try {
            $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
            
            $payload = [
                'model' => $model,
                'stream' => false,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $base64Image,
                                ],
                            ],
                        ],
                    ],
                ],
                'temperature' => 0.1,
            ];

            Log::info("Sending single receipt parsing request ({$priceType}) to 9router endpoint: {$endpoint} using model: {$model}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(45)->post($endpoint, $payload);

            $responseBody = $response->body();
            Log::info('9router raw API response: ' . mb_substr($responseBody, 0, 300));

            if ($response->failed()) {
                Log::error('9router API request failed with status: ' . $response->status() . ' Body: ' . $responseBody);
                return $this->emptyFallback("Gagal menghubungi AI Server 9router (Status: {$response->status()}). Response: " . mb_substr($responseBody, 0, 200));
            }

            $rawText = '';

            // Handle SSE (Server-Sent Events) stream chunks if 9router sends streamed data
            if (str_contains($responseBody, 'data:') || str_contains($responseBody, 'chat.completion.chunk')) {
                $lines = explode("\n", $responseBody);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, 'data:')) {
                        $jsonStr = trim(substr($line, 5));
                        if ($jsonStr === '[DONE]') {
                            break;
                        }
                        $chunk = json_decode($jsonStr, true);
                        if (isset($chunk['choices'][0]['delta']['content'])) {
                            $rawText .= $chunk['choices'][0]['delta']['content'];
                        } elseif (isset($chunk['choices'][0]['message']['content'])) {
                            $rawText .= $chunk['choices'][0]['message']['content'];
                        } elseif (isset($chunk['choices'][0]['text'])) {
                            $rawText .= $chunk['choices'][0]['text'];
                        }
                    }
                }
            } else {
                // Handle standard single JSON response object
                $responseData = $response->json();
                if (isset($responseData['choices'][0]['message']['content'])) {
                    $contentObj = $responseData['choices'][0]['message']['content'];
                    if (is_string($contentObj)) {
                        $rawText = $contentObj;
                    } elseif (is_array($contentObj)) {
                        foreach ($contentObj as $part) {
                            if (is_array($part) && isset($part['text'])) {
                                $rawText .= $part['text'] . "\n";
                            } elseif (is_string($part)) {
                                $rawText .= $part . "\n";
                            }
                        }
                    }
                } elseif (isset($responseData['choices'][0]['text'])) {
                    $rawText = $responseData['choices'][0]['text'];
                }
            }

            if (empty(trim($rawText))) {
                Log::warning('9router rawText is empty after parsing. Full response: ' . $responseBody);
                return $this->emptyFallback('AI 9router mengembalikan respon kosong. Detail: ' . mb_substr($responseBody, 0, 250));
            }

            // Extract JSON block using regex matching outer {...}
            $parsed = null;
            if (preg_match('/\{[\s\S]*\}/', $rawText, $matches)) {
                $jsonCandidate = $matches[0];
                $parsed = json_decode($jsonCandidate, true);
            }

            if (!is_array($parsed)) {
                Log::warning('Failed to parse JSON from AI response text: ' . $rawText);
                return $this->emptyFallback('Respon AI tidak berformat JSON yang valid. Teks: ' . mb_substr($rawText, 0, 150));
            }

            $rawItems = array_map(function ($item) {
                return [
                    'name' => (string) ($item['name'] ?? 'Item'),
                    'qty' => (int) max(1, $item['qty'] ?? 1),
                    'price' => (float) max(0, $item['price'] ?? 0),
                ];
            }, $parsed['items'] ?? []);

            $deliveryFee = (float) max(0, $parsed['delivery_fee'] ?? 0);
            $serviceFee = (float) max(0, $parsed['service_fee'] ?? 0);
            $discount = (float) max(0, $parsed['discount'] ?? 0);
            $total = (float) max(0, $parsed['total'] ?? 0);

            // Perform Price Format Enforcing or Auto-Crosscheck
            $processed = $this->applyPriceFormatRules($rawItems, $priceType, $deliveryFee, $serviceFee, $discount, $total);

            return [
                'success' => true,
                'merchant_name' => $parsed['merchant_name'] ?? 'Toko / Restoran',
                'items' => $processed['items'],
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'discount' => $discount,
                'total' => $total,
                'auto_corrected' => $processed['auto_corrected'],
                'auto_corrected_message' => $processed['auto_corrected_message'],
            ];

        } catch (\Throwable $e) {
            Log::error('Exception during receipt parsing: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->emptyFallback('Error: ' . $e->getMessage());
        }
    }

    /**
     * Apply user-defined price format rules or run mathematical crosscheck in auto mode.
     */
    protected function applyPriceFormatRules(array $items, string $priceType, float $deliveryFee, float $serviceFee, float $discount, float $total): array
    {
        if (empty($items)) {
            return [
                'items' => [],
                'auto_corrected' => false,
                'auto_corrected_message' => '',
            ];
        }

        if ($priceType === 'total_price') {
            // User explicitly requested Total Price mode: Force divide by qty for multi-qty items
            $corrected = [];
            $wasAdjusted = false;
            foreach ($items as $item) {
                if ($item['qty'] > 1) {
                    $unitPrice = round($item['price'] / $item['qty']);
                    $corrected[] = array_merge($item, ['price' => $unitPrice]);
                    $wasAdjusted = true;
                } else {
                    $corrected[] = $item;
                }
            }

            return [
                'items' => $corrected,
                'auto_corrected' => $wasAdjusted,
                'auto_corrected_message' => 'Format diterapkan (Harga Total Kuantitas): Harga item secara otomatis dibagi dengan jumlah kuantitas untuk menghasilkan harga satuan.',
            ];
        }

        if ($priceType === 'unit_price') {
            // User explicitly requested Unit Price mode: Do not alter prices
            return [
                'items' => $items,
                'auto_corrected' => false,
                'auto_corrected_message' => '',
            ];
        }

        // Default 'auto' mode: Run mathematical crosscheck
        return $this->crosscheckAndCorrectItems($items, $deliveryFee, $serviceFee, $discount, $total);
    }

    /**
     * Mathematical Auto-Crosscheck & Correction between Item Subtotals and Receipt Total.
     */
    protected function crosscheckAndCorrectItems(array $items, float $deliveryFee, float $serviceFee, float $discount, float $total): array
    {
        if ($total <= 0 || empty($items)) {
            return [
                'items' => $items,
                'auto_corrected' => false,
                'auto_corrected_message' => '',
            ];
        }

        $expectedItemsSubtotal = max(0, $total - ($deliveryFee + $serviceFee - $discount));

        $rawItemsSubtotal = 0;
        $multiQtyCount = 0;

        foreach ($items as $item) {
            $rawItemsSubtotal += ($item['qty'] * $item['price']);
            if ($item['qty'] > 1) {
                $multiQtyCount++;
            }
        }

        if ($expectedItemsSubtotal > 0 && $rawItemsSubtotal > ($expectedItemsSubtotal * 1.2) && $multiQtyCount > 0) {

            $adjustedSubtotal = 0;
            $testItems = [];

            foreach ($items as $item) {
                if ($item['qty'] > 1) {
                    $unitPrice = round($item['price'] / $item['qty']);
                    $testItems[] = array_merge($item, ['price' => $unitPrice]);
                    $adjustedSubtotal += ($item['qty'] * $unitPrice);
                } else {
                    $testItems[] = $item;
                    $adjustedSubtotal += ($item['qty'] * $item['price']);
                }
            }

            $diffRaw = abs($rawItemsSubtotal - $expectedItemsSubtotal);
            $diffAdjusted = abs($adjustedSubtotal - $expectedItemsSubtotal);

            if ($diffAdjusted < $diffRaw && $diffAdjusted <= ($expectedItemsSubtotal * 0.1)) {
                Log::info("Mathematical Crosscheck: Auto-corrected items unit price. Raw subtotal: {$rawItemsSubtotal}, Adjusted subtotal: {$adjustedSubtotal}, Expected subtotal: {$expectedItemsSubtotal}");

                return [
                    'items' => $testItems,
                    'auto_corrected' => true,
                    'auto_corrected_message' => 'Auto-Crosscheck AI: Sistem secara otomatis menyesuaikan harga satuan item (misal: 2x Rp 10.000 disesuaikan menjadi Rp 5.000/item) agar cocok dengan total belanja di struk.',
                ];
            }
        }

        return [
            'items' => $items,
            'auto_corrected' => false,
            'auto_corrected_message' => '',
        ];
    }

    /**
     * PHP-level secondary deduplication safeguard for multi-screenshot overlap.
     */
    protected function deduplicateItems(array $items): array
    {
        $unique = [];
        foreach ($items as $item) {
            $normalizedName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $item['name'])));
            $normalizedKey = $normalizedName . '_' . (int) $item['price'];

            if (isset($unique[$normalizedKey])) {
                $unique[$normalizedKey]['qty'] = max($unique[$normalizedKey]['qty'], $item['qty']);
            } else {
                $unique[$normalizedKey] = $item;
            }
        }
        return array_values($unique);
    }

    private function emptyFallback(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'merchant_name' => '',
            'items' => [],
            'delivery_fee' => 0,
            'service_fee' => 0,
            'discount' => 0,
            'total' => 0,
            'auto_corrected' => false,
            'auto_corrected_message' => '',
        ];
    }
}
