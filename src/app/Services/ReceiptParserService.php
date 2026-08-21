<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReceiptParserService
{
    /**
     * Parse receipt image using 9router AI Vision API.
     */
    public function parseImage(string $filePath): array
    {
        $apiKey = config('services.ninerouter.api_key', env('NINEROUTER_API_KEY', ''));
        $baseUrl = config('services.ninerouter.api_base', env('NINEROUTER_API_BASE', 'https://api.9router.com/v1'));
        $model = config('services.ninerouter.model', env('NINEROUTER_MODEL', 'gemma4-31b'));

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File struk tidak ditemukan di server.');
        }

        $imageBytes = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath) ?: 'image/jpeg';
        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageBytes);

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

Aturan Penting:
1. `price` pada item adalah HARGA SATUAN (unit price) dalam Rupiah (angka bulat tanpa desimal atau titik/koma).
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
                'stream' => false, // Explicitly request non-streaming response
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

            Log::info("Sending receipt parsing request to 9router endpoint: {$endpoint} using model: {$model}");

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($endpoint, $payload);

            $responseBody = $response->body();
            Log::info('9router raw API response: ' . $responseBody);

            if ($response->failed()) {
                Log::error('9router API request failed with status: ' . $response->status() . ' Body: ' . $responseBody);
                return $this->emptyFallback("Gagal menghubungi AI Server 9router (Status: {$response->status()}). Response: {$responseBody}");
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

            return [
                'success' => true,
                'merchant_name' => $parsed['merchant_name'] ?? 'Toko / Restoran',
                'items' => array_map(function ($item) {
                    return [
                        'name' => (string) ($item['name'] ?? 'Item'),
                        'qty' => (int) max(1, $item['qty'] ?? 1),
                        'price' => (float) max(0, $item['price'] ?? 0),
                    ];
                }, $parsed['items'] ?? []),
                'delivery_fee' => (float) max(0, $parsed['delivery_fee'] ?? 0),
                'service_fee' => (float) max(0, $parsed['service_fee'] ?? 0),
                'discount' => (float) max(0, $parsed['discount'] ?? 0),
                'total' => (float) max(0, $parsed['total'] ?? 0),
            ];

        } catch (\Throwable $e) {
            Log::error('Exception during receipt parsing: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->emptyFallback('Error: ' . $e->getMessage());
        }
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
        ];
    }
}
