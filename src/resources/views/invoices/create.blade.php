@extends('layouts.app')

@section('title', 'Buat Invoice Baru - PayMe')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <!-- Page Header -->
        <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="fw-bold fs-3 text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                    <span>Invoice Maker</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-6 rounded-pill px-2 py-1 align-middle" style="font-size: 0.75rem !important;">Beta</span>
                </h1>
                <p class="text-muted mb-0">Buat surat tagihan dan faktur profesional untuk klien atau pelanggan Anda dalam hitungan detik.</p>
            </div>
            <a href="{{ route('bills.create') }}" class="btn btn-outline-secondary btn-sm btn-pill px-3 shadow-xs">
                <i class="fa-solid fa-users me-1"></i> Beralih ke Split Bill
            </a>
        </div>

        <!-- Beta Info Banner -->
        <div class="alert alert-warning border border-warning-subtle bg-warning-subtle text-dark rounded-4 shadow-xs p-3 mb-4 d-flex align-items-center gap-3">
            <div class="flex-shrink-0">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill">BETA</span>
            </div>
            <div class="small mb-0">
                Fitur <strong>Invoice Maker</strong> saat ini dalam status <strong>Beta</strong>. Anda dapat membuat invoice instan dengan QRIS Dinamis & Multi Rekening secara gratis. Jika Anda menemukan kendala atau memiliki saran, silakan beri masukan!
            </div>
        </div>

        <form action="{{ route('invoices.store') }}" method="POST" enctype="multipart/form-data" id="invoiceForm">
            @csrf

            <!-- 1. INFORMASI DASAR INVOICE -->
            <div class="card glass-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 fs-6">
                        <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">1</span>
                        <span>Informasi Faktur & Tanggal</span>
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small mb-1">Nomor Invoice <span class="text-danger">*</span></label>
                            <div class="input-group shadow-xs">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-hashtag"></i></span>
                                <input type="text" name="invoice_number" id="invoiceNumber" class="form-control border-start-0 ps-0 fw-bold text-primary" value="{{ old('invoice_number', $defaultInvoiceNumber) }}" required>
                            </div>
                            <div class="form-text small" style="font-size: 0.75rem;">Dapat diubah sesuai format penomoran Anda.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small mb-1">Tanggal Tagihan <span class="text-danger">*</span></label>
                            <div class="input-group shadow-xs">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-regular fa-calendar"></i></span>
                                <input type="date" name="invoice_date" class="form-control border-start-0 ps-0" value="{{ old('invoice_date', $todayDate) }}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary small mb-1">Jatuh Tempo (Opsional)</label>
                            <div class="input-group shadow-xs">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-regular fa-calendar-xmark"></i></span>
                                <input type="date" name="due_date" class="form-control border-start-0 ps-0" value="{{ old('due_date', $defaultDueDate) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DATA PENGIRIM & KLIEN -->
            <div class="row g-4 mb-4">
                <!-- Data Pengirim -->
                <div class="col-md-6">
                    <div class="card glass-card shadow-sm h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-6">
                                    <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">2</span>
                                    <span>Dari (Penerbit / Penjual)</span>
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-pill px-2 py-1" id="btnLoadSenderProfile" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Pakai Data Tersimpan
                                </button>
                            </div>

                            <div class="space-y-3 flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Nama Usaha / Nama Anda <span class="text-danger">*</span></label>
                                    <input type="text" name="sender_name" id="senderName" class="form-control shadow-xs" placeholder="Misal: Studio Kreatif / John Doe" value="{{ old('sender_name') }}" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary small mb-1">Email</label>
                                        <input type="email" name="sender_email" id="senderEmail" class="form-control shadow-xs" placeholder="usaha@email.com" value="{{ old('sender_email') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary small mb-1">No. WhatsApp</label>
                                        <input type="text" name="sender_phone" id="senderPhone" class="form-control shadow-xs" placeholder="08123456789" value="{{ old('sender_phone') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Alamat / Lokasi</label>
                                    <textarea name="sender_address" id="senderAddress" class="form-control shadow-xs" rows="2" placeholder="Alamat kantor / domisili pengirim...">{{ old('sender_address') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Logo Usaha (Opsional)</label>
                                    <input type="file" name="sender_logo" id="senderLogo" class="form-control shadow-xs" accept="image/*">
                                    <div class="form-text small" style="font-size: 0.73rem;">Format PNG / JPG (Maks. 5MB). Ditampilkan di header invoice.</div>
                                </div>

                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" id="saveSenderProfile" checked>
                                    <label class="form-check-label small text-muted" for="saveSenderProfile">
                                        Ingat data usaha saya di browser ini
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Klien -->
                <div class="col-md-6">
                    <div class="card glass-card shadow-sm h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 fs-6">
                                <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">3</span>
                                <span>Tagihan Kepada (Klien / Pembeli)</span>
                            </h5>

                            <div class="space-y-3 flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Nama Klien / Pelanggan <span class="text-danger">*</span></label>
                                    <input type="text" name="client_name" class="form-control shadow-xs" placeholder="Misal: Bapak Andy / CV Maju Mundur" value="{{ old('client_name') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Nama Perusahaan (Opsional)</label>
                                    <input type="text" name="client_company" class="form-control shadow-xs" placeholder="Misal: PT Teknologi Nusantara" value="{{ old('client_company') }}">
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary small mb-1">Email Klien</label>
                                        <input type="email" name="client_email" class="form-control shadow-xs" placeholder="klien@perusahaan.com" value="{{ old('client_email') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-secondary small mb-1">No. WhatsApp Klien</label>
                                        <input type="text" name="client_phone" class="form-control shadow-xs" placeholder="08123456789" value="{{ old('client_phone') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary small mb-1">Alamat Klien (Opsional)</label>
                                    <textarea name="client_address" class="form-control shadow-xs" rows="2" placeholder="Alamat pengiriman / penagihan klien...">{{ old('client_address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. RINCIAN BARANG / JASA (ITEMS) -->
            <div class="card glass-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-6">
                            <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">4</span>
                            <span>Rincian Barang & Jasa</span>
                        </h5>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs" id="btnAddItem">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-borderless align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr class="text-secondary small border-bottom" style="font-size: 0.8rem;">
                                    <th style="min-width: 260px;">Deskripsi / Nama Item <span class="text-danger">*</span></th>
                                    <th style="width: 120px;">Qty</th>
                                    <th style="min-width: 170px;">Harga Satuan (Rp) <span class="text-danger">*</span></th>
                                    <th style="min-width: 160px;" class="text-end">Total (Rp)</th>
                                    <th style="width: 45px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                <!-- Default Item Row 1 -->
                                <tr class="item-row border-bottom border-light">
                                    <td class="py-2">
                                        <input type="text" name="items[0][item_name]" class="form-control shadow-xs item-name" placeholder="Misal: Jasa Desain UI/UX Landing Page" required>
                                    </td>
                                    <td class="py-2">
                                        <input type="number" name="items[0][quantity]" class="form-control shadow-xs item-qty text-center" value="1" min="0.01" step="any" required>
                                    </td>
                                    <td class="py-2">
                                        <input type="number" name="items[0][unit_price]" class="form-control shadow-xs item-price" placeholder="0" min="0" step="any" required>
                                    </td>
                                    <td class="py-2 text-end fw-bold text-dark item-total fs-6">
                                        Rp 0
                                    </td>
                                    <td class="py-2 text-center">
                                        <button type="button" class="btn btn-light text-danger btn-pill p-2 shadow-xs btn-remove-item" title="Hapus item">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Ringkasan Perhitungan Keuangan -->
                    <div class="row justify-content-end pt-3 border-top">
                        <div class="col-md-7 col-lg-6">
                            <div class="bg-light bg-opacity-70 rounded-4 p-4 shadow-xs">
                                <!-- Subtotal -->
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-secondary fw-semibold">Subtotal</span>
                                    <span class="fw-bold text-dark fs-6" id="displaySubtotal">Rp 0</span>
                                </div>

                                <!-- Diskon Input -->
                                <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                    <span class="text-secondary fw-semibold">Diskon</span>
                                    <div class="input-group shadow-xs" style="max-width: 220px;">
                                        <input type="number" name="discount_value" id="discountValue" class="form-control text-end" value="0" min="0" step="any">
                                        <select name="discount_type" id="discountType" class="form-select text-center fw-bold" style="max-width: 85px;">
                                            <option value="fixed">Rp</option>
                                            <option value="percent">%</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Pajak / PPN Input -->
                                <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                    <span class="text-secondary fw-semibold">Pajak / PPN</span>
                                    <div class="input-group shadow-xs" style="max-width: 160px;">
                                        <input type="number" name="tax_rate" id="taxRate" class="form-control text-end" value="0" min="0" max="100" step="any">
                                        <span class="input-group-text bg-white text-muted">%</span>
                                    </div>
                                </div>

                                <!-- Biaya Pengiriman / Biaya Lainnya -->
                                <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                    <span class="text-secondary fw-semibold">Ongkir / Biaya Tambahan</span>
                                    <div class="input-group shadow-xs" style="max-width: 220px;">
                                        <span class="input-group-text bg-white text-muted">Rp</span>
                                        <input type="number" name="shipping_fee" id="shippingFee" class="form-control text-end" value="0" min="0" step="any">
                                    </div>
                                </div>

                                <hr class="my-3 border-secondary border-opacity-25">

                                <!-- Grand Total -->
                                <div class="d-flex justify-content-between align-items-center pt-1">
                                    <span class="fw-bold text-dark fs-5">Total Tagihan</span>
                                    <span class="fw-extrabold text-primary fs-4" id="displayGrandTotal">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. METODE PEMBAYARAN (MULTI BANK & QRIS DINAMIS) -->
            <div class="card glass-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 fs-6">
                        <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">5</span>
                        <span>Informasi & Opsi Pembayaran (Opsional)</span>
                    </h5>

                    <div class="row g-4">
                        <!-- Multi Rekening Bank / E-Wallet -->
                        <div class="col-md-7 border-end-md pe-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-primary"></i>
                                    <span>Rekening Bank / E-Wallet</span>
                                </h6>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs" id="btnAddBank">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Bank
                                </button>
                            </div>

                            <div id="banksListContainer" class="vstack gap-3 mb-3">
                                <!-- Default Bank Row 1 -->
                                <div class="bank-row card bg-light border border-light-subtle p-3 rounded-4 position-relative">
                                    <div class="row g-3">
                                        <div class="col-sm-5">
                                            <label class="form-label text-secondary small mb-1 fw-semibold">Nama Bank / E-Wallet</label>
                                            <input type="text" name="banks[0][bank_name]" class="form-control bank-name-input shadow-xs" placeholder="Misal: BCA / Mandiri / GoPay" value="{{ old('bank_name') }}">
                                        </div>
                                        <div class="col-sm-7">
                                            <label class="form-label text-secondary small mb-1 fw-semibold">Nomor Rekening / No. HP</label>
                                            <input type="text" name="banks[0][account_number]" class="form-control bank-acc-input shadow-xs" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label text-secondary small mb-1 fw-semibold">Atas Nama Pemilik Rekening</label>
                                            <div class="d-flex gap-2">
                                                <input type="text" name="banks[0][account_holder]" class="form-control bank-holder-input shadow-xs" placeholder="Contoh: PT Aku Online Indonesia" value="{{ old('bank_account_holder') }}">
                                                <button type="button" class="btn btn-outline-danger btn-remove-bank shadow-xs px-3" title="Hapus bank">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload QRIS Gambar untuk Dynamic QRIS Generator -->
                        <div class="col-md-5 ps-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-qrcode text-primary"></i>
                                    <span>Upload QRIS Statis</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-pill px-2 py-1" id="btnLoadSavedQris" style="font-size: 0.72rem; display: none;">
                                    <i class="fa-solid fa-bolt text-warning me-1"></i> Pakai QRIS Tersimpan
                                </button>
                            </div>

                            <p class="text-muted small mb-3" style="font-size: 0.78rem;">
                                Unggah gambar QRIS Usaha Anda. Sistem akan mengonversinya secara otomatis menjadi <strong>QRIS Dinamis</strong> ber-nominal pas saat invoice diterbitkan.
                            </p>

                            <div class="mb-3">
                                <label class="form-label text-secondary small mb-1 fw-semibold">Pilih Gambar QRIS (PNG / JPG)</label>
                                <input type="file" name="qris_image" id="qrisImageInput" class="form-control shadow-xs" accept="image/*">
                            </div>

                            <!-- Hidden field to hold parsed QRIS payload -->
                            <input type="hidden" name="qris_static_payload" id="qrisStaticPayload" value="{{ old('qris_static_payload') }}">

                            <div id="qrisPreviewContainer" class="d-none text-center p-3 bg-light rounded-4 border border-light-subtle">
                                <canvas id="qrisCanvas" class="d-none img-fluid rounded border mb-2" style="max-height: 130px;"></canvas>

                                <div id="qrisValidState" class="d-none">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 mb-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> QRIS Valid Terdeteksi
                                    </span>
                                    <h6 id="qrisMerchantNameText" class="fw-bold text-dark mb-0 mt-1"></h6>
                                    <small id="qrisMerchantCityText" class="text-muted d-block"></small>
                                </div>

                                <div id="qrisInvalidState" class="d-none">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 mb-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Format Tidak Dikenali
                                    </span>
                                    <small id="qrisInvalidReason" class="text-muted d-block">Gambar bukan QRIS standar atau tidak terbaca dengan jelas.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. CATATAN & SYARAT KETENTUAN -->
            <div class="card glass-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 fs-6">
                        <span class="badge bg-primary text-white rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">6</span>
                        <span>Catatan Tambahan & Syarat Ketentuan</span>
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small mb-1 fw-semibold">Catatan untuk Klien</label>
                            <textarea name="notes" class="form-control shadow-xs" rows="3" placeholder="Terima kasih atas kerja sama Anda. Harap konfirmasi jika pembayaran telah dilakukan.">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small mb-1 fw-semibold">Syarat & Ketentuan (Terms)</label>
                            <textarea name="terms" class="form-control shadow-xs" rows="3" placeholder="1. Pembayaran diselesaikan sebelum tanggal jatuh tempo.&#10;2. Bukti transfer mohon dikirimkan via WhatsApp.">{{ old('terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mb-5">
                <div class="text-muted small">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Invoice akan langsung terbit dan dapat dibagikan atau dicetak sebagai PDF.
                </div>
                <button type="submit" class="btn btn-gradient-primary btn-lg btn-pill px-5 py-3 fw-bold shadow d-flex align-items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Buat & Terbitkan Invoice</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceForm = document.getElementById('invoiceForm');
    const itemsContainer = document.getElementById('itemsContainer');
    const btnAddItem = document.getElementById('btnAddItem');
    const discountType = document.getElementById('discountType');
    const discountValue = document.getElementById('discountValue');
    const taxRate = document.getElementById('taxRate');
    const shippingFee = document.getElementById('shippingFee');
    const displaySubtotal = document.getElementById('displaySubtotal');
    const displayGrandTotal = document.getElementById('displayGrandTotal');

    let itemIndex = itemsContainer.querySelectorAll('.item-row').length;

    // Helper: format IDR
    function formatRupiah(number) {
        return 'Rp ' + Math.round(number).toLocaleString('id-ID');
    }

    // Auto calculate totals
    function calculateInvoiceTotals() {
        let subtotal = 0;
        const rows = itemsContainer.querySelectorAll('.item-row');

        rows.forEach(row => {
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const totalCell = row.querySelector('.item-total');

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            const rowTotal = qty * price;
            subtotal += rowTotal;

            if (totalCell) {
                totalCell.textContent = formatRupiah(rowTotal);
            }
        });

        if (displaySubtotal) {
            displaySubtotal.textContent = formatRupiah(subtotal);
        }

        // Discount
        const discType = discountType ? discountType.value : 'fixed';
        const discVal = parseFloat(discountValue ? discountValue.value : 0) || 0;
        let discAmount = 0;
        if (discVal > 0) {
            if (discType === 'percent') {
                discAmount = subtotal * (Math.min(100, discVal) / 100);
            } else {
                discAmount = Math.min(subtotal, discVal);
            }
        }

        // Tax
        const taxVal = parseFloat(taxRate ? taxRate.value : 0) || 0;
        const taxableBase = Math.max(0, subtotal - discAmount);
        const taxAmount = taxVal > 0 ? (taxableBase * (taxVal / 100)) : 0;

        // Shipping
        const shipping = parseFloat(shippingFee ? shippingFee.value : 0) || 0;

        const grandTotal = Math.max(0, subtotal - discAmount + taxAmount + shipping);
        if (displayGrandTotal) {
            displayGrandTotal.textContent = formatRupiah(grandTotal);
        }
    }

    // Add Item Row
    if (btnAddItem) {
        btnAddItem.addEventListener('click', function () {
            const tr = document.createElement('tr');
            tr.className = 'item-row border-bottom border-light';
            tr.innerHTML = `
                <td class="py-2">
                    <input type="text" name="items[${itemIndex}][item_name]" class="form-control shadow-xs item-name" placeholder="Deskripsi barang atau jasa" required>
                </td>
                <td class="py-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control shadow-xs item-qty text-center" value="1" min="0.01" step="any" required>
                </td>
                <td class="py-2">
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control shadow-xs item-price" placeholder="0" min="0" step="any" required>
                </td>
                <td class="py-2 text-end fw-bold text-dark item-total fs-6">
                    Rp 0
                </td>
                <td class="py-2 text-center">
                    <button type="button" class="btn btn-light text-danger btn-pill p-2 shadow-xs btn-remove-item" title="Hapus item">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            itemsContainer.appendChild(tr);
            itemIndex++;
            const nameInput = tr.querySelector('.item-name');
            if (nameInput) nameInput.focus();
            calculateInvoiceTotals();
        });
    }

    // Remove Item Row via delegation
    if (itemsContainer) {
        itemsContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-item');
            if (btn) {
                const rows = itemsContainer.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    btn.closest('.item-row').remove();
                    calculateInvoiceTotals();
                } else {
                    alert('Minimal harus ada satu item dalam invoice.');
                }
            }
        });
    }

    // Global form input delegation for instant calculation
    if (invoiceForm) {
        invoiceForm.addEventListener('input', calculateInvoiceTotals);
        invoiceForm.addEventListener('change', calculateInvoiceTotals);
    }

    // Initial calculation
    calculateInvoiceTotals();

    // Multi-Bank Rows Logic
    const banksListContainer = document.getElementById('banksListContainer');
    const btnAddBank = document.getElementById('btnAddBank');
    let bankIndex = banksListContainer ? banksListContainer.querySelectorAll('.bank-row').length : 1;

    if (btnAddBank && banksListContainer) {
        btnAddBank.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'bank-row card bg-light border border-light-subtle p-3 rounded-4 position-relative';
            div.innerHTML = `
                <div class="row g-3">
                    <div class="col-sm-5">
                        <label class="form-label text-secondary small mb-1 fw-semibold">Nama Bank / E-Wallet</label>
                        <input type="text" name="banks[${bankIndex}][bank_name]" class="form-control bank-name-input shadow-xs" placeholder="Misal: BCA / Mandiri / GoPay">
                    </div>
                    <div class="col-sm-7">
                        <label class="form-label text-secondary small mb-1 fw-semibold">Nomor Rekening / No. HP</label>
                        <input type="text" name="banks[${bankIndex}][account_number]" class="form-control bank-acc-input shadow-xs" placeholder="Contoh: 1234567890">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-secondary small mb-1 fw-semibold">Atas Nama Pemilik Rekening</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="banks[${bankIndex}][account_holder]" class="form-control bank-holder-input shadow-xs" placeholder="Contoh: PT Aku Online Indonesia">
                            <button type="button" class="btn btn-outline-danger btn-remove-bank shadow-xs px-3" title="Hapus bank">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            banksListContainer.appendChild(div);
            bankIndex++;
        });

        banksListContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-bank');
            if (btn) {
                const rows = banksListContainer.querySelectorAll('.bank-row');
                if (rows.length > 1) {
                    btn.closest('.bank-row').remove();
                } else {
                    const inputs = btn.closest('.bank-row').querySelectorAll('input');
                    inputs.forEach(i => i.value = '');
                }
            }
        });
    }

    // LocalStorage Sender Profile
    const STORAGE_KEY = 'payme_invoice_sender_profile';
    const senderName = document.getElementById('senderName');
    const senderEmail = document.getElementById('senderEmail');
    const senderPhone = document.getElementById('senderPhone');
    const senderAddress = document.getElementById('senderAddress');
    const saveSenderCheckbox = document.getElementById('saveSenderProfile');
    const btnLoadSenderProfile = document.getElementById('btnLoadSenderProfile');

    function loadSavedSenderProfile() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                if (senderName && data.senderName) senderName.value = data.senderName;
                if (senderEmail && data.senderEmail) senderEmail.value = data.senderEmail;
                if (senderPhone && data.senderPhone) senderPhone.value = data.senderPhone;
                if (senderAddress && data.senderAddress) senderAddress.value = data.senderAddress;
            }
        } catch (e) {
            console.warn('Cannot load sender profile', e);
        }
    }

    if (btnLoadSenderProfile) {
        const existingProfile = localStorage.getItem(STORAGE_KEY);
        if (!existingProfile) {
            btnLoadSenderProfile.style.display = 'none';
        } else if (senderName && !senderName.value) {
            loadSavedSenderProfile();
        }

        btnLoadSenderProfile.addEventListener('click', function () {
            loadSavedSenderProfile();
        });
    }

    // Save profile on form submit
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', function () {
            if (saveSenderCheckbox && saveSenderCheckbox.checked) {
                const profile = {
                    senderName: senderName ? senderName.value : '',
                    senderEmail: senderEmail ? senderEmail.value : '',
                    senderPhone: senderPhone ? senderPhone.value : '',
                    senderAddress: senderAddress ? senderAddress.value : '',
                };
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(profile));
                } catch (e) {}
            }
        });
    }

    // QRIS Image Upload & EMVCo Decoding
    const qrisImageInput = document.getElementById('qrisImageInput');
    const qrisStaticPayload = document.getElementById('qrisStaticPayload');
    const qrisPreviewContainer = document.getElementById('qrisPreviewContainer');
    const qrisCanvas = document.getElementById('qrisCanvas');
    const qrisValidState = document.getElementById('qrisValidState');
    const qrisInvalidState = document.getElementById('qrisInvalidState');
    const qrisMerchantNameText = document.getElementById('qrisMerchantNameText');
    const qrisMerchantCityText = document.getElementById('qrisMerchantCityText');
    const btnLoadSavedQris = document.getElementById('btnLoadSavedQris');

    // Helper: Parse Merchant info from EMVCo payload string
    function parseQrisInfo(code) {
        let merchantName = 'Merchant QRIS';
        let merchantCity = '';
        if (!code || typeof code !== 'string') return { valid: false };

        const str = code.trim();
        if (!str.startsWith('000201')) return { valid: false };

        const nameMatch = str.match(/59(\d{2})([^\d]{2,})/);
        if (nameMatch) {
            const len = parseInt(nameMatch[1], 10);
            merchantName = nameMatch[2].substring(0, len);
        }

        const cityMatch = str.match(/60(\d{2})([^\d]{2,})/);
        if (cityMatch) {
            const len = parseInt(cityMatch[1], 10);
            merchantCity = cityMatch[2].substring(0, len);
        }

        return {
            valid: true,
            merchantName: merchantName.trim(),
            merchantCity: merchantCity.trim()
        };
    }

    function processQrisFile(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = new Image();
            img.onload = function () {
                if (!qrisCanvas) return;
                qrisCanvas.width = img.width;
                qrisCanvas.height = img.height;
                const ctx = qrisCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);

                const imageData = ctx.getImageData(0, 0, qrisCanvas.width, qrisCanvas.height);
                const code = (typeof jsQR !== 'undefined') ? jsQR(imageData.data, imageData.width, imageData.height) : null;

                if (qrisPreviewContainer) qrisPreviewContainer.classList.remove('d-none');
                qrisCanvas.classList.remove('d-none');

                if (code && code.data) {
                    const parsed = parseQrisInfo(code.data);
                    if (parsed.valid) {
                        qrisStaticPayload.value = code.data;
                        if (qrisValidState) qrisValidState.classList.remove('d-none');
                        if (qrisInvalidState) qrisInvalidState.classList.add('d-none');

                        if (qrisMerchantNameText) qrisMerchantNameText.textContent = parsed.merchantName;
                        if (qrisMerchantCityText) qrisMerchantCityText.textContent = parsed.merchantCity ? 'Lokasi: ' + parsed.merchantCity : '';

                        // Save to payme_saved_qris
                        try {
                            localStorage.setItem('payme_saved_qris', JSON.stringify({
                                payload: code.data,
                                merchantName: parsed.merchantName,
                                merchantCity: parsed.merchantCity
                            }));
                        } catch (e) {}
                    } else {
                        qrisStaticPayload.value = '';
                        if (qrisValidState) qrisValidState.classList.add('d-none');
                        if (qrisInvalidState) qrisInvalidState.classList.remove('d-none');
                    }
                } else {
                    qrisStaticPayload.value = '';
                    if (qrisValidState) qrisValidState.classList.add('d-none');
                    if (qrisInvalidState) qrisInvalidState.classList.remove('d-none');
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    if (qrisImageInput) {
        qrisImageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            processQrisFile(file);
        });
    }

    // Check saved QRIS in localStorage (compatible with split bill saved QRIS)
    try {
        const savedQrisRaw = localStorage.getItem('payme_saved_qris');
        if (savedQrisRaw && btnLoadSavedQris) {
            const savedQris = JSON.parse(savedQrisRaw);
            if (savedQris && savedQris.payload) {
                btnLoadSavedQris.style.display = 'inline-block';
                btnLoadSavedQris.addEventListener('click', function () {
                    qrisStaticPayload.value = savedQris.payload;
                    if (qrisPreviewContainer) qrisPreviewContainer.classList.remove('d-none');
                    if (qrisValidState) qrisValidState.classList.remove('d-none');
                    if (qrisInvalidState) qrisInvalidState.classList.add('d-none');
                    if (qrisMerchantNameText) qrisMerchantNameText.textContent = savedQris.merchantName || 'Merchant QRIS';
                    if (qrisMerchantCityText) qrisMerchantCityText.textContent = savedQris.merchantCity ? 'Lokasi: ' + savedQris.merchantCity : '';
                });
            }
        }
    } catch (e) {}
});
</script>
@endpush
