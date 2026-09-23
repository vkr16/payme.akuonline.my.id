<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\QrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Show the invoice creation form.
     */
    public function create()
    {
        $defaultInvoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $todayDate = date('Y-m-d');
        $defaultDueDate = date('Y-m-d', strtotime('+7 days'));

        return view('invoices.create', compact('defaultInvoiceNumber', 'todayDate', 'defaultDueDate'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request, QrisService $qrisService)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'sender_phone' => 'nullable|string|max:50',
            'sender_address' => 'nullable|string|max:1000',
            'sender_logo' => 'nullable|image|max:5120',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_address' => 'nullable|string|max:1000',
            'client_company' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_holder' => 'nullable|string|max:100',
            'banks' => 'nullable|array',
            'banks.*.bank_name' => 'nullable|string|max:100',
            'banks.*.account_number' => 'nullable|string|max:100',
            'banks.*.account_holder' => 'nullable|string|max:100',
            'qris_image' => 'nullable|image|max:10240',
            'qris_static_payload' => 'nullable|string',
            'discount_type' => 'nullable|string|in:fixed,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        // File uploads
        $logoPath = null;
        if ($request->hasFile('sender_logo')) {
            $logoPath = $request->file('sender_logo')->store('invoices/logos', 'public');
        }

        $qrisImagePath = null;
        if ($request->hasFile('qris_image')) {
            $qrisImagePath = $request->file('qris_image')->store('invoices/qris', 'public');
        }

        // Calculate totals server-side
        $subtotal = 0;
        $cleanItems = [];
        $order = 0;

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $cleanItems[] = [
                'item_name' => $item['item_name'],
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $lineTotal,
                'sort_order' => $order++,
            ];
        }

        $discountType = $validated['discount_type'] ?? 'fixed';
        $discountVal = (float) ($validated['discount_value'] ?? 0);
        $discountAmount = 0;

        if ($discountVal > 0) {
            if ($discountType === 'percent') {
                $discountAmount = round($subtotal * (min(100, $discountVal) / 100), 2);
            } else {
                $discountAmount = min($subtotal, $discountVal);
            }
        }

        $taxRate = (float) ($validated['tax_rate'] ?? 0);
        $taxableAmount = max(0, $subtotal - $discountAmount);
        $taxAmount = $taxRate > 0 ? round($taxableAmount * ($taxRate / 100), 2) : 0;

        $shippingFee = (float) ($validated['shipping_fee'] ?? 0);
        $totalAmount = max(0, $subtotal - $discountAmount + $taxAmount + $shippingFee);

        $payload = $validated['qris_static_payload'] ?? null;
        if ($payload && !$qrisService->isValidQris($payload)) {
            $payload = null;
        }

        // Process Banks
        $banksList = [];
        if (!empty($validated['banks']) && is_array($validated['banks'])) {
            foreach ($validated['banks'] as $b) {
                if (!empty($b['bank_name']) && !empty($b['account_number'])) {
                    $banksList[] = [
                        'bank_name' => trim($b['bank_name']),
                        'account_number' => trim($b['account_number']),
                        'account_holder' => trim($b['account_holder'] ?? ''),
                    ];
                }
            }
        }

        // Fallback for single bank inputs
        if (empty($banksList) && !empty($validated['bank_name']) && !empty($validated['bank_account_number'])) {
            $banksList[] = [
                'bank_name' => trim($validated['bank_name']),
                'account_number' => trim($validated['bank_account_number']),
                'account_holder' => trim($validated['bank_account_holder'] ?? ''),
            ];
        }

        $primaryBank = $banksList[0] ?? [
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['bank_account_number'] ?? null,
            'account_holder' => $validated['bank_account_holder'] ?? null,
        ];

        $invoice = Invoice::create([
            'slug' => Str::random(10),
            'edit_token' => Str::random(32),
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'unpaid',
            'sender_name' => $validated['sender_name'],
            'sender_email' => $validated['sender_email'] ?? null,
            'sender_phone' => $validated['sender_phone'] ?? null,
            'sender_address' => $validated['sender_address'] ?? null,
            'sender_logo_path' => $logoPath,
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'client_address' => $validated['client_address'] ?? null,
            'client_company' => $validated['client_company'] ?? null,
            'bank_name' => $primaryBank['bank_name'] ?? null,
            'bank_account_number' => $primaryBank['account_number'] ?? null,
            'bank_account_holder' => $primaryBank['account_holder'] ?? null,
            'qris_image_path' => $qrisImagePath,
            'qris_static_payload' => $payload,
            'subtotal' => $subtotal,
            'discount_type' => $discountType,
            'discount_value' => $discountVal,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'shipping_fee' => $shippingFee,
            'total_amount' => $totalAmount,
            'currency' => 'IDR',
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        foreach ($cleanItems as $cleanItem) {
            $invoice->items()->create($cleanItem);
        }

        foreach ($banksList as $b) {
            $invoice->banks()->create($b);
        }

        // Store edit token in session
        session(['invoice_token_' . $invoice->slug => $invoice->edit_token]);

        return redirect('/i/' . $invoice->slug)
            ->with('success', 'Invoice #' . $invoice->invoice_number . ' berhasil dibuat!')
            ->with('new_passcode', $invoice->passcode)
            ->with('created_token', $invoice->edit_token);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Request $request, string $slug, QrisService $qrisService)
    {
        $invoice = Invoice::with(['items', 'banks'])->where('slug', $slug)->firstOrFail();

        // Check if user has owner privileges (via token in query, header, or session)
        $sessionToken = session('invoice_token_' . $slug);
        $queryToken = $request->query('token');

        if ($queryToken && hash_equals($invoice->edit_token, (string) $queryToken)) {
            session(['invoice_token_' . $slug => $queryToken]);
            $isOwner = true;
        } elseif ($sessionToken && hash_equals($invoice->edit_token, (string) $sessionToken)) {
            $isOwner = true;
        } else {
            $isOwner = false;
        }

        // Generate dynamic QRIS string if static payload is present
        $dynamicQrisPayload = null;
        $merchantInfo = null;
        if ($invoice->qris_static_payload && $invoice->total_amount > 0) {
            try {
                $dynamicQrisPayload = $qrisService->convertToDynamic($invoice->qris_static_payload, $invoice->total_amount);
                $merchantInfo = $qrisService->extractMerchantInfo($invoice->qris_static_payload);
            } catch (\Throwable $e) {
                $dynamicQrisPayload = $invoice->qris_static_payload;
            }
        }

        $newPasscode = session('new_passcode');

        return view('invoices.show', compact('invoice', 'isOwner', 'dynamicQrisPayload', 'merchantInfo', 'newPasscode'));
    }

    /**
     * Serve Sender Logo Image safely.
     */
    public function logoImage(string $slug)
    {
        $invoice = Invoice::where('slug', $slug)->firstOrFail();

        if (empty($invoice->sender_logo_path)) {
            abort(404, 'Logo tidak ditemukan.');
        }

        if (Storage::disk('public')->exists($invoice->sender_logo_path)) {
            return Storage::disk('public')->response($invoice->sender_logo_path);
        }

        $fullPath = storage_path('app/public/' . $invoice->sender_logo_path);
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        }

        abort(404, 'File logo tidak ditemukan.');
    }

    /**
     * Serve QRIS Image safely.
     */
    public function qrisImage(string $slug)
    {
        $invoice = Invoice::where('slug', $slug)->firstOrFail();

        if (empty($invoice->qris_image_path)) {
            abort(404, 'Gambar QRIS tidak ditemukan.');
        }

        if (Storage::disk('public')->exists($invoice->qris_image_path)) {
            return Storage::disk('public')->response($invoice->qris_image_path);
        }

        $fullPath = storage_path('app/public/' . $invoice->qris_image_path);
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        }

        abort(404, 'File gambar QRIS tidak ditemukan.');
    }

    /**
     * Update status of invoice with passcode verification.
     */
    public function updateStatus(Request $request, string $slug): JsonResponse
    {
        $invoice = Invoice::where('slug', $slug)->firstOrFail();

        $passcode = trim((string) $request->input('passcode'));
        $token = $request->input('token') ?? session('invoice_token_' . $slug);

        $isAuthorized = false;
        if (!empty($passcode) && $invoice->passcode) {
            $isAuthorized = hash_equals(strtoupper($invoice->passcode), strtoupper($passcode));
        }

        if (!$isAuthorized && !empty($token) && $invoice->edit_token) {
            $isAuthorized = hash_equals($invoice->edit_token, (string) $token);
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi salah. Silakan periksa kembali kata sandi invoice Anda.',
            ], 422);
        }

        $request->validate([
            'status' => 'required|in:unpaid,paid,cancelled',
        ]);

        $status = $request->input('status');
        $invoice->status = $status;
        $invoice->paid_at = ($status === 'paid') ? now() : null;
        $invoice->save();

        // Remember ownership for this session
        session(['invoice_token_' . $slug => $invoice->edit_token]);

        return response()->json([
            'success' => true,
            'status' => $invoice->status,
            'status_label' => $invoice->status_label,
            'status_badge_class' => $invoice->status_badge_class,
            'message' => 'Status invoice berhasil diubah menjadi: ' . $invoice->status_label,
        ]);
    }
}
