<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillClaim;
use App\Models\BillItem;
use App\Services\QrisService;
use App\Services\ReceiptParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillController extends Controller
{
    /**
     * Show create bill form.
     */
    public function create()
    {
        return view('bills.create');
    }

    /**
     * Parse receipt image using AI Vision (9router).
     */
    public function parseReceipt(Request $request, ReceiptParserService $parser): JsonResponse
    {
        $request->validate([
            'receipt_image' => 'required|image|max:10240', // Max 10MB
        ]);

        $file = $request->file('receipt_image');
        $tempPath = $file->getRealPath();

        $result = $parser->parseImage($tempPath);

        return response()->json($result);
    }

    /**
     * Store newly created bill in database.
     */
    public function store(Request $request, QrisService $qrisService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'host_name' => 'required|string|max:255',
            'qris_static_payload' => 'nullable|string',
            'qris_image' => 'nullable|image|max:10240',
            'receipt_image' => 'nullable|image|max:10240',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:100',
            'delivery_fee' => 'nullable|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Upload images if present
        $qrisImagePath = null;
        if ($request->hasFile('qris_image')) {
            $qrisImagePath = $request->file('qris_image')->store('bills/qris', 'public');
        }

        $receiptImagePath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptImagePath = $request->file('receipt_image')->store('bills/receipts', 'public');
        }

        $payload = $validated['qris_static_payload'] ?? '';
        $merchantName = null;
        $merchantCity = null;

        if ($payload) {
            $merchantInfo = $qrisService->extractMerchantInfo($payload);
            $merchantName = $merchantInfo['merchant_name'];
            $merchantCity = $merchantInfo['merchant_city'];
        }

        $bill = Bill::create([
            'slug' => Str::random(8),
            'title' => $validated['title'],
            'host_name' => $validated['host_name'],
            'qris_static_payload' => $payload,
            'qris_merchant_name' => $merchantName,
            'qris_merchant_city' => $merchantCity,
            'qris_image_path' => $qrisImagePath,
            'receipt_image_path' => $receiptImagePath,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'delivery_fee' => $validated['delivery_fee'] ?? 0,
            'service_fee' => $validated['service_fee'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
        ]);

        foreach ($validated['items'] as $itemData) {
            $bill->items()->create([
                'name' => $itemData['name'],
                'qty' => (int) $itemData['qty'],
                'price' => (float) $itemData['price'],
            ]);
        }

        return redirect()->route('bills.show', $bill->slug)
            ->with('success', 'Patungan berhasil dibuat!');
    }

    /**
     * Show bill page for participants.
     */
    public function show(string $slug)
    {
        $bill = Bill::with(['items.claimItems', 'claims.claimItems.item'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('bills.show', compact('bill'));
    }

    /**
     * Calculate nominal & generate dynamic QRIS for selected items.
     */
    public function generateDynamicQris(Request $request, string $slug, QrisService $qrisService): JsonResponse
    {
        $bill = Bill::with(['items.claimItems'])->where('slug', $slug)->firstOrFail();

        $selectedItems = $request->input('items', []); // format: [item_id => quantity]

        $itemsSubtotal = 0;

        foreach ($bill->items as $item) {
            $claimedQty = (int) ($selectedItems[$item->id] ?? 0);
            if ($claimedQty > 0) {
                // Ensure claimedQty does not exceed remaining unpaid qty
                $validQty = min($claimedQty, $item->remaining_qty);
                $itemsSubtotal += ($validQty * $item->price);
            }
        }

        $totalBillSubtotal = $bill->subtotal;
        $netExtraFees = $bill->net_extra_fees;

        // Proportional fee calculation
        $feeShare = 0;
        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $feeShare = $proportion * $netExtraFees;
        }

        $totalPayable = round($itemsSubtotal + $feeShare);
        if ($totalPayable < 0) {
            $totalPayable = 0;
        }

        $dynamicQrisPayload = '';
        if (!empty($bill->qris_static_payload)) {
            $dynamicQrisPayload = $qrisService->convertToDynamic($bill->qris_static_payload, $totalPayable);
        }

        return response()->json([
            'success' => true,
            'items_subtotal' => $itemsSubtotal,
            'fee_share' => $feeShare,
            'total_payable' => $totalPayable,
            'dynamic_qris_payload' => $dynamicQrisPayload,
            'merchant_name' => $bill->qris_merchant_name ?: $bill->host_name,
            'merchant_city' => $bill->qris_merchant_city ?: '-',
            'bank_info' => [
                'bank_name' => $bill->bank_name,
                'account_number' => $bill->bank_account_number,
                'account_holder' => $bill->bank_account_holder,
            ],
        ]);
    }

    /**
     * Submit payment claim ("Saya Sudah Bayar").
     */
    public function claimPayment(Request $request, string $slug): JsonResponse
    {
        $bill = Bill::with(['items.claimItems'])->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'payer_name' => 'required|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*' => 'required|integer|min:1',
        ]);

        $payerName = trim($validated['payer_name']);
        $paymentMethod = $validated['payment_method'] ?? 'qris';
        $selectedItems = $validated['items'];

        $itemsSubtotal = 0;
        $validItemsToClaim = [];

        foreach ($bill->items as $item) {
            $requestedQty = (int) ($selectedItems[$item->id] ?? 0);
            if ($requestedQty > 0) {
                // Ensure requestedQty does not exceed remaining unpaid qty
                $claimableQty = min($requestedQty, $item->remaining_qty);
                if ($claimableQty > 0) {
                    $validItemsToClaim[$item->id] = $claimableQty;
                    $itemsSubtotal += ($claimableQty * $item->price);
                }
            }
        }

        if (empty($validItemsToClaim)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item valid atau sisa item yang belum dibayar.',
            ], 422);
        }

        $totalBillSubtotal = $bill->subtotal;
        $netExtraFees = $bill->net_extra_fees;

        $feeShare = 0;
        if ($totalBillSubtotal > 0 && $itemsSubtotal > 0) {
            $proportion = $itemsSubtotal / $totalBillSubtotal;
            $feeShare = $proportion * $netExtraFees;
        }

        $totalPaid = round($itemsSubtotal + $feeShare);

        // Save BillClaim
        $claim = BillClaim::create([
            'bill_id' => $bill->id,
            'payer_name' => $payerName,
            'amount' => $totalPaid,
            'payment_method' => $paymentMethod,
        ]);

        // Save BillClaimItems
        foreach ($validItemsToClaim as $itemId => $qty) {
            $claim->claimItems()->create([
                'bill_item_id' => $itemId,
                'qty' => $qty,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Terima kasih {$payerName}! Konfirmasi pembayaran berhasil disimpan.",
            'amount' => $totalPaid,
        ]);
    }
}
