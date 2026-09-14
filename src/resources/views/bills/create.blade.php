@extends('layouts.app')

@section('title', 'Buat Patungan Baru - PayMe')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="fw-bold fs-3 text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-receipt text-primary"></i>
                <span>Buat Patungan Baru</span>
            </h1>
            <p class="text-muted mb-0">Bagi tagihan pesanan secara adil dan transparan dengan konversi QRIS statis ke dinamis serta pembagian proporsional.</p>
        </div>

        <!-- PRIVACY & DATA RETENTION CTA BANNER (TOP POSITION WITH P-3 PADDING) -->
        <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-xs mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 bg-primary text-white rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-user-shield fs-6"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 fs-6">Jaminan Privasi & Retensi Data Otomatis</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">Data disimpan max {{ config('payme.retention.paid_days', 3) }} hari (lunas) / {{ config('payme.retention.unpaid_days', 7) }} hari (aktif) & dihapus permanen pukul 00:00 WIB.</p>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-pill px-3 fw-semibold flex-shrink-0 shadow-xs" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">
                <i class="fa-solid fa-shield-halved me-1"></i> Pelajari Retensi
            </button>
        </div>

        <!-- QUICK ACCESS: BUAT QR DINAMIS INSTANT BANNER -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(13, 148, 136, 0.08) 100%); border: 1px solid rgba(2, 132, 199, 0.22) !important;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-4 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 46px; height: 46px; background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);">
                            <i class="fa-solid fa-bolt-lightning fs-5 text-warning"></i>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="fw-bold text-dark mb-0 fs-6">Hanya Perlu QRIS Dinamis Instan?</h5>
                                <span class="badge bg-primary text-white px-2 py-1 small fw-semibold" style="font-size: 0.68rem;">Quick Access</span>
                            </div>
                            <p class="text-muted small mb-0" style="font-size: 0.82rem;">
                                Tidak ingin membuat rincian patungan & struk? Ubah QRIS statis kamu jadi QRIS dinamis ber-nominal presisi dalam hitungan detik.
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-gradient-primary btn-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-xs flex-shrink-0" data-bs-toggle="modal" data-bs-target="#instantQrisModal">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Buat QR Dinamis Instant</span>
                    </button>
                </div>
            </div>
        </div>

        <form action="{{ route('bills.store') }}" method="POST" enctype="multipart/form-data" id="billForm">
            @csrf

            <!-- STEP 1: INFORMASI PATUNGAN -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-users text-primary"></i>
                        <span>Langkah 1: Informasi Patungan</span>
                    </h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Nama Penggalang (Ditalangi Oleh) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="host_name" class="form-control border-start-0" placeholder="Contoh: Fikri" value="{{ old('host_name') }}" required autocomplete="name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Nama Acara / Pesanan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-utensils"></i></span>
                                <input type="text" name="title" class="form-control border-start-0" placeholder="Contoh: Makan Siang Tim IT" value="{{ old('title') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: UPLOAD QRIS STATIS -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-qrcode text-primary"></i>
                        <span>Langkah 2: QRIS Penerima Pembayaran</span>
                    </h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small">
                        <i class="fa-solid fa-bolt me-1"></i> Auto-Detect
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- Banner QRIS Tersimpan dari Browser -->
                    <div id="savedQrisBanner" class="alert alert-light border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-3 p-3 mb-3 d-none">
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small d-flex align-items-center flex-wrap gap-2">
                                        <span>QRIS Terakhir Dimuat Otomatis</span>
                                        <span class="badge bg-success bg-opacity-15 text-white border border-success border-opacity-25 px-1.5 py-0.5" style="font-size: 0.7rem;">Tersimpan di Browser</span>
                                    </div>
                                    <div class="text-muted small" id="savedQrisMerchantInfo">
                                        Merchant: <strong id="savedQrisMerchantName">-</strong> <span id="savedQrisMerchantCity" class="text-muted"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 w-sm-auto justify-content-end mt-2 mt-sm-0">
                                <button type="button" class="btn btn-sm btn-white bg-white border text-dark shadow-sm d-flex align-items-center gap-1" id="btnChangeQris">
                                    <i class="fa-solid fa-arrow-rotate-right text-primary"></i>
                                    <span>Ganti QRIS</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" id="btnForgetQris" title="Hapus QRIS tersimpan dari browser">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <label class="upload-dropzone w-100 mb-0" for="qrisFileInput" id="qrisDropzone">
                                <i class="fa-solid fa-cloud-arrow-up text-primary fs-2 mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block mb-1">Pilih Gambar QRIS Statis Kamu</span>
                                <span class="text-muted small">Format PNG, JPG, atau JPEG (Maksimal 10 MB)</span>
                                <input type="file" name="qris_image" id="qrisFileInput" class="d-none" accept="image/*">
                            </label>
                            <!-- Hidden input for raw QRIS EMVCo string payload -->
                            <input type="hidden" name="qris_static_payload" id="qrisStaticPayload" value="{{ old('qris_static_payload') }}">
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 rounded-3 bg-light border text-center" id="qrisPreviewContainer">
                                <div id="qrisEmptyState" class="py-3 text-muted small">
                                    <i class="fa-solid fa-image me-1"></i> Belum ada gambar QRIS dipilih
                                </div>
                                <canvas id="qrisCanvas" class="d-none img-fluid rounded border mb-2" style="max-height: 160px;"></canvas>

                                <!-- State: QRIS Valid -->
                                <div id="qrisValidState" class="d-none">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 mb-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> QRIS Valid
                                    </span>
                                    <h6 id="merchantNameText" class="fw-bold text-dark mb-0 mt-1"></h6>
                                    <small id="merchantCityText" class="text-muted"></small>
                                </div>

                                <!-- State: QRIS Invalid / Bukan QRIS -->
                                <div id="qrisInvalidState" class="d-none">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 mb-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Bukan QRIS Valid
                                    </span>
                                    <h6 id="qrisInvalidTitle" class="fw-bold text-danger mb-0 mt-1">Format Tidak Dikenali</h6>
                                    <small id="qrisInvalidReason" class="text-muted d-block"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: STRUK PESANAN & AI SCANNER -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-primary"></i>
                        <span>Langkah 3: Rincian Struk & Item Pesanan</span>
                    </h5>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- User Defined Price Format Dropdown -->
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="fa-solid fa-sliders"></i>
                            </span>
                            <select id="receiptPriceTypeSelect" class="form-select form-select-sm border-start-0 fw-medium" title="Format Harga pada Struk">
                                <option value="unit_price" selected>Harga Struk = Harga Satuan</option>
                                <option value="total_price">Harga Struk = Harga Total Item</option>
                            </select>
                        </div>

                        <label for="receiptFileInput" class="btn btn-sm btn-gradient-primary btn-pill px-3 mb-0 cursor-pointer d-flex align-items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Pindai Struk via AI Vision</span>
                            <input type="file" name="receipt_images[]" id="receiptFileInput" class="d-none" accept="image/*" multiple>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-flex align-items-center gap-1" id="btnAddManualItem">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Item</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">

                    <!-- Loading Indicator for AI -->
                    <div id="aiLoading" class="alert alert-info d-none text-center py-3 mb-4 border-0 shadow-sm">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span class="fw-semibold" id="aiLoadingText">Menganalisis gambar struk via AI Vision... Harap tunggu sebentar.</span>
                    </div>

                    <!-- Alert message -->
                    <div id="aiAlert" class="alert d-none rounded-3 mb-3 border-0 shadow-sm"></div>

                    <!-- Mobile-Friendly Items Card List (No Table!) -->
                    <div id="itemsContainer" class="vstack gap-2">
                        <!-- Dynamic item cards -->
                    </div>

                    <div id="noItemsState" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-list-check fs-3 mb-2 d-block text-secondary"></i>
                        Belum ada item. Silakan pindai struk atau klik <strong>Tambah Item</strong>.
                    </div>

                    <!-- Subtotal & Extra Fees -->
                    <div class="border-top mt-4 pt-4">
                        <div class="row g-3 justify-content-end">
                            <div class="col-md-7 col-lg-6">
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Subtotal Item:</span>
                                        <span class="fw-bold text-dark" id="displayItemsSubtotal">Rp 0</span>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label text-muted small mb-1">Ongkos Kirim</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white text-muted">Rp</span>
                                                <input type="text" inputmode="numeric" name="delivery_fee" id="inputDeliveryFee" class="form-control form-control-sm currency-input" placeholder="0" value="{{ old('delivery_fee', 0) }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small mb-1">Biaya Layanan</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white text-muted">Rp</span>
                                                <input type="text" inputmode="numeric" name="service_fee" id="inputServiceFee" class="form-control form-control-sm currency-input" placeholder="0" value="{{ old('service_fee', 0) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Total Diskon / Voucher</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white text-success fw-bold">Rp</span>
                                            <input type="text" inputmode="numeric" name="discount" id="inputDiscount" class="form-control form-control-sm text-success fw-bold currency-input" placeholder="0" value="{{ old('discount', 0) }}">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="fw-bold text-dark small">Grand Total Struk:</span>
                                        <span class="fw-bold text-primary fs-4" id="displayGrandTotal">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- STEP 4: TRANSFER BANK & E-WALLET ALTERNATIF (MULTI-BANK) -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building-columns text-primary"></i>
                        <span>Langkah 4: Rekening Bank / Dompet Digital Alternatif (Opsional)</span>
                    </h5>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggleBankOption">
                        <label class="form-check-label text-muted small cursor-pointer" for="toggleBankOption">Aktifkan Rekening Bank</label>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4 d-none" id="bankFieldsContainer">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <p class="text-muted small mb-0">Tambahkan opsi transfer rekening bank atau dompet digital (seperti BCA, Mandiri, GoPay, OVO, ShopeePay, dll).</p>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small d-none" id="bankSavedBadge">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Data Tersimpan Dimuat
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-pill px-2.5 d-none" id="btnClearSavedBanks" title="Hapus rekening tersimpan dari browser">
                                <i class="fa-solid fa-trash-can me-1"></i> Hapus Tersimpan
                            </button>
                            <button type="button" class="btn btn-sm btn-gradient-primary btn-pill px-3 d-flex align-items-center gap-2 shadow-sm" id="btnAddBankRow">
                                <i class="fa-solid fa-plus"></i>
                                <span>Tambah Rekening</span>
                            </button>
                        </div>
                    </div>

                    <div id="banksListContainer" class="vstack gap-3">
                        <!-- Dynamic Bank Cards -->
                    </div>
                </div>
            </div>

            <!-- SUBMIT ACTION -->
            <div class="d-flex justify-content-end mb-5">
                <button type="button" class="btn btn-gradient-primary btn-lg btn-pill w-100 w-sm-auto px-5 py-3 shadow-md d-flex align-items-center justify-content-center gap-2 fw-bold fs-6" id="btnOpenConfirmModal">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Simpan & Buat Link Patungan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMATION MODAL BEFORE BILL CREATION -->
<div class="modal fade" id="confirmBillModal" tabindex="-1" aria-labelledby="confirmBillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal border-0">
            <div class="modal-header border-bottom border-light py-3">
                <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center" id="confirmBillModalLabel">
                    <i class="fa-solid fa-clipboard-check text-primary me-2 fs-5"></i>
                    <span>Konfirmasi Target Tagihan Patungan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary bg-primary bg-opacity-10 border border-primary border-opacity-25 text-dark rounded-3 mb-3 small d-flex align-items-start gap-2">
                    <i class="fa-solid fa-circle-info text-primary fs-5 flex-shrink-0 mt-1"></i>
                    <div>Pastikan rincian item dan total tagihan sudah sesuai. Ekstraksi struk diproses otomatis oleh AI dan berpotensi memuat kekeliruan.</div>
                </div>

                <!-- Host & Bill Title Summary -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 p-sm-3 bg-white rounded-3 border">
                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Penggalang (Host)</small>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" id="confirmHostName">-</h6>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 p-sm-3 bg-white rounded-3 border">
                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Nama Pesanan</small>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" id="confirmBillTitle">-</h6>
                        </div>
                    </div>
                </div>

                <!-- Items Preview List -->
                <div class="card border mb-3">
                    <div class="card-header bg-light py-2 px-3 fw-semibold small text-muted d-flex justify-content-between">
                        <span>Daftar Menu (<span id="confirmItemCount">0</span> item)</span>
                        <span>Total Nominal</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 160px; overflow-y: auto;">
                        <ul class="list-group list-group-flush small" id="confirmItemsList">
                            <!-- Populated via JS -->
                        </ul>
                    </div>
                </div>

                <!-- Price Breakdown & Grand Total -->
                <div class="p-3 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-1 small text-muted">
                        <span>Subtotal Item:</span>
                        <span class="fw-semibold text-dark" id="confirmItemsSubtotal">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small text-muted" id="confirmDeliveryFeeRow">
                        <span>Ongkos Kirim:</span>
                        <span class="fw-semibold text-dark" id="confirmDeliveryFee">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small text-muted" id="confirmServiceFeeRow">
                        <span>Biaya Layanan / Admin:</span>
                        <span class="fw-semibold text-dark" id="confirmServiceFee">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 small text-muted" id="confirmDiscountRow">
                        <span>Diskon Promo:</span>
                        <span class="fw-semibold text-danger" id="confirmDiscount">-Rp 0</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold text-dark d-block">TOTAL TARGET TAGIHAN</span>
                            <small class="text-muted" style="font-size: 0.72rem;">Nominal yang akan dibagi ke anggota</small>
                        </div>
                        <span class="fw-extrabold text-primary fs-4" id="confirmGrandTotal">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-light py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-pill px-4" data-bs-dismiss="modal">
                    <i class="fa-solid fa-pen me-1"></i> Cek Kembali
                </button>
                <button type="button" class="btn btn-gradient-primary btn-pill px-4 shadow-sm" id="btnFinalSubmit">
                    <i class="fa-solid fa-paper-plane me-1"></i> Ya, Buat Tagihan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    let bankIndex = 0;

    const billForm = document.getElementById('billForm');
    const qrisFileInput = document.getElementById('qrisFileInput');
    const qrisStaticPayload = document.getElementById('qrisStaticPayload');
    const qrisCanvas = document.getElementById('qrisCanvas');
    const qrisEmptyState = document.getElementById('qrisEmptyState');
    const qrisMerchantInfo = document.getElementById('qrisMerchantInfo');
    const merchantNameText = document.getElementById('merchantNameText');
    const merchantCityText = document.getElementById('merchantCityText');

    const receiptFileInput = document.getElementById('receiptFileInput');
    const aiLoading = document.getElementById('aiLoading');
    const aiLoadingText = document.getElementById('aiLoadingText');
    const aiAlert = document.getElementById('aiAlert');

    const btnAddManualItem = document.getElementById('btnAddManualItem');
    const itemsContainer = document.getElementById('itemsContainer');
    const noItemsState = document.getElementById('noItemsState');

    const inputDeliveryFee = document.getElementById('inputDeliveryFee');
    const inputServiceFee = document.getElementById('inputServiceFee');
    const inputDiscount = document.getElementById('inputDiscount');
    const displayItemsSubtotal = document.getElementById('displayItemsSubtotal');
    const displayGrandTotal = document.getElementById('displayGrandTotal');

    const toggleBankOption = document.getElementById('toggleBankOption');
    const bankFieldsContainer = document.getElementById('bankFieldsContainer');
    const btnAddBankRow = document.getElementById('btnAddBankRow');
    const banksListContainer = document.getElementById('banksListContainer');

    // Helper functions for thousand formatting
    function formatThousand(val) {
        if (val === null || val === undefined || val === '') return '';
        const digits = String(val).replace(/\D/g, '');
        if (!digits) return '';
        return parseInt(digits, 10).toLocaleString('id-ID');
    }

    function parseRawNumber(val) {
        if (!val) return 0;
        const digits = String(val).replace(/\D/g, '');
        return parseInt(digits, 10) || 0;
    }

    function attachCurrencyFormatter(input) {
        if (input.value) {
            input.value = formatThousand(input.value);
        }
        input.addEventListener('input', function() {
            const raw = parseRawNumber(this.value);
            this.value = raw > 0 ? formatThousand(raw) : '';
            recalculateTotals();
        });
    }

    // Attach currency formatter to static extra fee inputs
    [inputDeliveryFee, inputServiceFee, inputDiscount].forEach(input => {
        attachCurrencyFormatter(input);
    });

    // Multi-Bank Row logic & Browser Persistence
    const bankSavedBadge = document.getElementById('bankSavedBadge');
    const btnClearSavedBanks = document.getElementById('btnClearSavedBanks');

    function addBankRow(bankName = '', accountNumber = '', accountHolder = '') {
        const card = document.createElement('div');
        card.className = 'card border shadow-sm bank-row';
        card.dataset.index = bankIndex;

        card.innerHTML = `
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-dark small fw-semibold mb-1">Nama Bank / Dompet Digital</label>
                        <input type="text" name="banks[${bankIndex}][bank_name]" class="form-control form-control-sm" placeholder="Contoh: BCA / GoPay" value="${escapeHtml(bankName)}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label text-dark small fw-semibold mb-1">Nomor Rekening / HP</label>
                        <input type="text" name="banks[${bankIndex}][account_number]" class="form-control form-control-sm" placeholder="Contoh: 1234567890" value="${escapeHtml(accountNumber)}">
                    </div>
                    <div class="col-10 col-md-3">
                        <label class="form-label text-dark small fw-semibold mb-1">Atas Nama (a/n)</label>
                        <input type="text" name="banks[${bankIndex}][account_holder]" class="form-control form-control-sm" placeholder="Contoh: John Doe" value="${escapeHtml(accountHolder)}">
                    </div>
                    <div class="col-2 col-md-1 text-end pt-3">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-bank p-2" title="Hapus Rekening">
                            <i class="fa-solid fa-trash-can fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        banksListContainer.appendChild(card);
        bankIndex++;

        card.querySelectorAll('input').forEach(input => {
            input.disabled = !toggleBankOption.checked;
        });

        card.querySelector('.btn-delete-bank').addEventListener('click', () => {
            card.remove();
            saveBanksToStorage();
        });
    }

    function enableBankInputs(enabled) {
        document.querySelectorAll('#banksListContainer input').forEach(input => {
            input.disabled = !enabled;
        });
    }

    function loadSavedBanks() {
        try {
            const raw = localStorage.getItem('payme_saved_banks');
            if (!raw) return false;
            const banks = JSON.parse(raw);
            if (!Array.isArray(banks) || banks.length === 0) return false;

            banksListContainer.innerHTML = '';
            banks.forEach(b => {
                if (b && (b.bank_name || b.account_number || b.account_holder)) {
                    addBankRow(b.bank_name || '', b.account_number || '', b.account_holder || '');
                }
            });

            if (banksListContainer.children.length > 0) {
                if (bankSavedBadge) bankSavedBadge.classList.remove('d-none');
                if (btnClearSavedBanks) btnClearSavedBanks.classList.remove('d-none');
                return true;
            }
        } catch (e) {
            console.error('Gagal memuat bank dari localStorage:', e);
        }
        return false;
    }

    function saveBanksToStorage() {
        if (!toggleBankOption.checked) return;
        const banks = [];
        document.querySelectorAll('#banksListContainer .bank-row').forEach(row => {
            const bankName = row.querySelector('input[name*="[bank_name]"]')?.value?.trim() || '';
            const accNum = row.querySelector('input[name*="[account_number]"]')?.value?.trim() || '';
            const accHolder = row.querySelector('input[name*="[account_holder]"]')?.value?.trim() || '';
            if (bankName || accNum || accHolder) {
                banks.push({
                    bank_name: bankName,
                    account_number: accNum,
                    account_holder: accHolder
                });
            }
        });

        try {
            if (banks.length > 0) {
                localStorage.setItem('payme_saved_banks', JSON.stringify(banks));
                if (btnClearSavedBanks) btnClearSavedBanks.classList.remove('d-none');
            } else {
                localStorage.removeItem('payme_saved_banks');
                if (bankSavedBadge) bankSavedBadge.classList.add('d-none');
                if (btnClearSavedBanks) btnClearSavedBanks.classList.add('d-none');
            }
        } catch (e) {}
    }

    btnAddBankRow.addEventListener('click', function() {
        addBankRow('', '', '');
    });

    banksListContainer.addEventListener('input', function() {
        saveBanksToStorage();
    });

    if (btnClearSavedBanks) {
        btnClearSavedBanks.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Hapus daftar rekening bank yang tersimpan di browser ini?')) {
                try {
                    localStorage.removeItem('payme_saved_banks');
                } catch (e) {}
                if (bankSavedBadge) bankSavedBadge.classList.add('d-none');
                btnClearSavedBanks.classList.add('d-none');
            }
        });
    }

    toggleBankOption.addEventListener('change', function() {
        if (this.checked) {
            bankFieldsContainer.classList.remove('d-none');
            enableBankInputs(true);
            if (banksListContainer.children.length === 0) {
                const loaded = loadSavedBanks();
                if (!loaded) {
                    addBankRow('', '', '');
                }
            }
        } else {
            bankFieldsContainer.classList.add('d-none');
            enableBankInputs(false);
        }
    });

    const qrisDropzone = document.getElementById('qrisDropzone');
    const qrisValidState = document.getElementById('qrisValidState');
    const qrisInvalidState = document.getElementById('qrisInvalidState');
    const qrisInvalidTitle = document.getElementById('qrisInvalidTitle');
    const qrisInvalidReason = document.getElementById('qrisInvalidReason');

    // Saved QRIS Elements
    const savedQrisBanner = document.getElementById('savedQrisBanner');
    const savedQrisMerchantName = document.getElementById('savedQrisMerchantName');
    const savedQrisMerchantCity = document.getElementById('savedQrisMerchantCity');
    const btnChangeQris = document.getElementById('btnChangeQris');
    const btnForgetQris = document.getElementById('btnForgetQris');

    function getOptimizedDataUrl(img, maxDim = 800) {
        let w = img.width;
        let h = img.height;
        if (w > maxDim || h > maxDim) {
            if (w > h) {
                h = Math.round((h * maxDim) / w);
                w = maxDim;
            } else {
                w = Math.round((w * maxDim) / h);
                h = maxDim;
            }
        }
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);
        return canvas.toDataURL('image/jpeg', 0.85);
    }

    function dataURLtoFile(dataurl, filename) {
        const arr = dataurl.split(',');
        const mimeMatch = arr[0].match(/:(.*?);/);
        const mime = mimeMatch ? mimeMatch[1] : 'image/jpeg';
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename, { type: mime });
    }

    function updateSavedQrisBanner(data) {
        if (!savedQrisBanner) return;
        if (data && data.payload) {
            if (savedQrisMerchantName) savedQrisMerchantName.innerText = data.merchantName || 'Merchant QRIS';
            if (savedQrisMerchantCity) savedQrisMerchantCity.innerText = data.merchantCity ? '(' + data.merchantCity + ')' : '';
            savedQrisBanner.classList.remove('d-none');
        } else {
            savedQrisBanner.classList.add('d-none');
        }
    }

    function generateQrOnCanvas(payload) {
        const tempDiv = document.createElement('div');
        if (typeof QRCode !== 'undefined') {
            new QRCode(tempDiv, {
                text: payload,
                width: 300,
                height: 300,
                correctLevel: QRCode.CorrectLevel.M
            });
            setTimeout(() => {
                const c = tempDiv.querySelector('canvas') || tempDiv.querySelector('img');
                if (c) {
                    const src = c.toDataURL ? c.toDataURL('image/png') : c.src;
                    const img = new Image();
                    img.onload = function() {
                        qrisCanvas.width = img.width;
                        qrisCanvas.height = img.height;
                        const ctx = qrisCanvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        qrisCanvas.classList.remove('d-none');

                        try {
                            const file = dataURLtoFile(src, 'qris_terakhir.png');
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            qrisFileInput.files = dt.files;
                        } catch (e) {}
                    };
                    img.src = src;
                }
            }, 60);
        }
    }

    function applySavedQris(data, validation) {
        qrisStaticPayload.value = data.payload;
        merchantNameText.innerText = validation.merchantName || data.merchantName || 'Merchant QRIS';
        merchantCityText.innerText = (validation.location || data.merchantCity) ? 'Lokasi: ' + (validation.location || data.merchantCity) : '';

        qrisEmptyState.classList.add('d-none');
        qrisValidState.classList.remove('d-none');
        qrisInvalidState.classList.add('d-none');

        updateSavedQrisBanner(data);

        const imageSource = data.imageData || data.thumbnail;
        if (imageSource) {
            const img = new Image();
            img.onload = function() {
                qrisCanvas.width = img.width;
                qrisCanvas.height = img.height;
                const ctx = qrisCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                qrisCanvas.classList.remove('d-none');

                try {
                    const file = dataURLtoFile(imageSource, 'qris_terakhir.jpg');
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    qrisFileInput.files = dt.files;
                } catch (err) {
                    console.warn('DataTransfer fallback:', err);
                }
            };
            img.src = imageSource;
        } else {
            generateQrOnCanvas(data.payload);
        }
    }

    function loadSavedQrisOnStartup() {
        try {
            const raw = localStorage.getItem('payme_saved_qris');
            if (!raw) return;
            const data = JSON.parse(raw);
            if (!data || !data.payload) return;

            const validation = validateQrisPayload(data.payload);
            if (!validation.valid) return;

            // If there is already a different payload from old server input, keep it
            if (qrisStaticPayload.value && qrisStaticPayload.value.trim().length > 0 && qrisStaticPayload.value !== data.payload) {
                return;
            }

            applySavedQris(data, validation);
        } catch (e) {
            console.error('Gagal memuat QRIS dari storage:', e);
        }
    }

    // Drag & Drop Event Listeners on QRIS Dropzone
    if (qrisDropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            qrisDropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                qrisDropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            qrisDropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                qrisDropzone.classList.remove('dragover');
            });
        });

        qrisDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                const file = dt.files[0];
                // Sync dropped file with input element for form submission
                try {
                    qrisFileInput.files = dt.files;
                } catch (err) {
                    // Fallback for older browsers
                }
                processQrisFile(file);
            }
        });
    }

    if (btnChangeQris) {
        btnChangeQris.addEventListener('click', function(e) {
            e.preventDefault();
            qrisFileInput.click();
        });
    }

    if (btnForgetQris) {
        btnForgetQris.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Hapus gambar QRIS yang tersimpan di browser ini?')) {
                try {
                    localStorage.removeItem('payme_saved_qris');
                } catch (e) {}
                if (savedQrisBanner) savedQrisBanner.classList.add('d-none');
                qrisFileInput.value = '';
                try {
                    qrisFileInput.files = new DataTransfer().files;
                } catch (e) {}
                qrisStaticPayload.value = '';
                qrisCanvas.classList.add('d-none');
                qrisValidState.classList.add('d-none');
                qrisInvalidState.classList.add('d-none');
                qrisEmptyState.classList.remove('d-none');
            }
        });
    }

    qrisFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            processQrisFile(file);
        }
    });

    function processQrisFile(file) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                qrisCanvas.width = img.width;
                qrisCanvas.height = img.height;
                const ctx = qrisCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);

                const imageData = ctx.getImageData(0, 0, qrisCanvas.width, qrisCanvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                qrisEmptyState.classList.add('d-none');
                qrisCanvas.classList.remove('d-none');

                if (code && code.data) {
                    const validation = validateQrisPayload(code.data);
                    if (validation.valid) {
                        qrisStaticPayload.value = code.data;
                        qrisValidState.classList.remove('d-none');
                        qrisInvalidState.classList.add('d-none');

                        merchantNameText.innerText = validation.merchantName || 'Merchant QRIS';
                        merchantCityText.innerText = validation.location ? 'Lokasi: ' + validation.location : '';

                        let thumbDataUrl = '';
                        try {
                            const thumbCanvas = document.createElement('canvas');
                            thumbCanvas.width = 120;
                            thumbCanvas.height = 120;
                            const tCtx = thumbCanvas.getContext('2d');
                            tCtx.drawImage(img, 0, 0, 120, 120);
                            thumbDataUrl = thumbCanvas.toDataURL('image/png');
                        } catch (e) {}

                        let optimizedDataUrl = '';
                        try {
                            optimizedDataUrl = getOptimizedDataUrl(img, 800);
                        } catch (e) {
                            optimizedDataUrl = thumbDataUrl;
                        }

                        const savedObj = {
                            payload: code.data,
                            merchantName: validation.merchantName || 'Merchant QRIS',
                            merchantCity: validation.location || 'Indonesia',
                            thumbnail: thumbDataUrl,
                            imageData: optimizedDataUrl,
                            updated_at: new Date().toISOString()
                        };

                        try {
                            localStorage.setItem('payme_saved_qris', JSON.stringify(savedObj));
                        } catch (err) {}

                        updateSavedQrisBanner(savedObj);
                    } else {
                        qrisStaticPayload.value = '';
                        qrisValidState.classList.add('d-none');
                        qrisInvalidState.classList.remove('d-none');

                        qrisInvalidTitle.innerText = 'Bukan Format QRIS Standar';
                        qrisInvalidReason.innerText = validation.reason || 'Kode QR yang terdeteksi bukan QRIS pembayaran Indonesia.';
                    }
                } else {
                    qrisStaticPayload.value = '';
                    qrisValidState.classList.add('d-none');
                    qrisInvalidState.classList.remove('d-none');

                    qrisInvalidTitle.innerText = 'Kode QR Tidak Terdeteksi';
                    qrisInvalidReason.innerText = 'Gambar yang diunggah bukan kode QR atau tidak terbaca dengan jelas.';
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    function validateQrisPayload(qrisStr) {
        if (!qrisStr || typeof qrisStr !== 'string') {
            return { valid: false, reason: 'Data QR kosong atau tidak terbaca.' };
        }

        const str = qrisStr.trim();
        if (!str.startsWith('000201')) {
            return { valid: false, reason: 'Kode QR bukan standar EMVCo / QRIS (tidak diawali 000201).' };
        }

        if (!str.includes('5802ID') && !str.includes('5303360')) {
            return { valid: false, reason: 'Kode QR bukan QRIS Indonesia (tidak memuat tag 5802ID / 5303360).' };
        }

        const nameMatch = str.match(/59(\d{2})([^\d]{2,})/);
        if (!nameMatch) {
            return { valid: false, reason: 'Tag nama merchant (59) tidak ditemukan di dalam QRIS.' };
        }

        const len = parseInt(nameMatch[1], 10);
        const merchantName = nameMatch[2].substring(0, len).trim();

        const locMatch = str.match(/60(\d{2})([^\d]{2,})/);
        const location = locMatch ? locMatch[2].substring(0, parseInt(locMatch[1], 10)).trim() : '';

        return {
            valid: true,
            merchantName: merchantName,
            location: location
        };
    }

    // Auto-load saved QRIS on page startup
    loadSavedQrisOnStartup();

    // Client-side 1-by-1 Sequential Loop for Multi-Image Receipt Scanning
    receiptFileInput.addEventListener('change', async function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        aiLoading.classList.remove('d-none');
        aiAlert.classList.add('d-none');

        let allItems = [];
        let maxDeliveryFee = 0;
        let maxServiceFee = 0;
        let maxDiscount = 0;
        let successCount = 0;

        let autoCorrectedFlags = [];

        const priceTypeSelect = document.getElementById('receiptPriceTypeSelect');
        const priceType = priceTypeSelect ? priceTypeSelect.value : 'auto';

        for (let i = 0; i < files.length; i++) {
            const currentFile = files[i];
            aiLoadingText.innerText = `Menganalisis gambar struk ${i + 1} dari ${files.length} via AI Vision... Harap tunggu sebentar.`;

            const formData = new FormData();
            formData.append('receipt_image', currentFile);
            formData.append('receipt_price_type', priceType);

            try {
                const response = await fetch("{{ route('bills.parse-receipt', [], false) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    successCount++;
                    if (data.items && data.items.length > 0) {
                        allItems = allItems.concat(data.items);
                    }
                    if (data.delivery_fee) maxDeliveryFee = Math.max(maxDeliveryFee, data.delivery_fee);
                    if (data.service_fee) maxServiceFee = Math.max(maxServiceFee, data.service_fee);
                    if (data.discount) maxDiscount = Math.max(maxDiscount, data.discount);
                    if (data.auto_corrected) autoCorrectedFlags.push(true);
                }
            } catch (err) {
                console.error(`Error processing image ${i + 1}:`, err);
            }
        }

        aiLoading.classList.add('d-none');

        if (successCount > 0 && allItems.length > 0) {
            // Perform Client-Side Deduplication across overlapping screenshot items
            const deduplicated = deduplicateItems(allItems);
            let hasAutoCorrected = autoCorrectedFlags.includes(true);

            let alertHtml = `<div class="d-flex align-items-center gap-2"><i class="fa-solid fa-wand-magic-sparkles text-success fs-5"></i> <span>AI berhasil memproses ${successCount} dari ${files.length} foto struk dan mengekstrak ${deduplicated.length} item.</span></div>`;
            if (hasAutoCorrected) {
                alertHtml += `<div class="mt-2 pt-2 border-top text-dark small"><i class="fa-solid fa-calculator text-primary me-1"></i> <strong>Pemeriksaan Otomatis:</strong> Format harga total baris terdeteksi dan dikoreksi ke harga satuan per item secara presisi.</div>`;
            }

            aiAlert.className = 'alert alert-success rounded-3 mb-3 border-0 shadow-sm';
            aiAlert.innerHTML = alertHtml;
            aiAlert.classList.remove('d-none');

            itemsContainer.innerHTML = '';
            itemIndex = 0;

            deduplicated.forEach(item => {
                addItemRow(item.name, item.qty, item.price);
            });

            if (maxDeliveryFee > 0) inputDeliveryFee.value = formatThousand(maxDeliveryFee);
            if (maxServiceFee > 0) inputServiceFee.value = formatThousand(maxServiceFee);
            if (maxDiscount > 0) inputDiscount.value = formatThousand(maxDiscount);

            recalculateTotals();

        } else {
            aiAlert.className = 'alert alert-warning rounded-3 mb-3 border-0 shadow-sm';
            aiAlert.innerHTML = `<div class="d-flex align-items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i> <span>Gagal mengekstrak item dari gambar struk. Silakan periksa kejelasan foto atau masukkan item secara manual.</span></div>`;
            aiAlert.classList.remove('d-none');
        }
    });

    // Deduplicate items algorithm across overlapping screenshots
    function deduplicateItems(items) {
        const uniqueMap = {};
        items.forEach(item => {
            const rawName = String(item.name || '').trim();
            const cleanName = rawName.replace(/\s+/g, ' ');
            if (!cleanName) return;

            const price = parseFloat(item.price) || 0;
            const normKey = normalizeItemKey(cleanName, price);

            if (uniqueMap[normKey]) {
                uniqueMap[normKey].qty = Math.max(uniqueMap[normKey].qty, parseInt(item.qty) || 1);
                if (cleanName.length > uniqueMap[normKey].name.length) {
                    uniqueMap[normKey].name = cleanName;
                }
            } else {
                uniqueMap[normKey] = {
                    ...item,
                    name: cleanName,
                    qty: Math.max(1, parseInt(item.qty) || 1),
                    price: price
                };
            }
        });
        return Object.values(uniqueMap);
    }

    function normalizeItemKey(name, price) {
        let str = String(name || '').toLowerCase();
        // Remove common metadata noise prefixes
        str = str.replace(/\b(catatan|notes?|opsi)\s*:\s*/gi, ' ');
        // Standardize unit abbreviations
        str = str.replace(/(\d+)\s*(gram|gr)\b/gi, '$1g');
        str = str.replace(/(\d+)\s*pcs\b/gi, '$1pcs');
        str = str.replace(/(\d+)\s*ml\b/gi, '$1ml');
        // Remove formatting punctuation/brackets/dashes
        str = str.replace(/[^a-z0-9\s]/gi, ' ');
        // Collapse whitespace
        str = str.replace(/\s+/g, ' ').trim();

        return str + '___' + Math.round(parseFloat(price) || 0);
    }

    btnAddManualItem.addEventListener('click', function() {
        addItemRow('', 1, 0);
    });

    function addItemRow(name = '', qty = 1, price = 0) {
        noItemsState.classList.add('d-none');

        const card = document.createElement('div');
        card.className = 'card border shadow-sm item-row';
        card.dataset.index = itemIndex;

        card.innerHTML = `
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <label class="form-label text-dark small mb-1 fw-semibold">Nama Item</label>
                        <input type="text" name="items[${itemIndex}][name]" class="form-control form-control-sm item-name" placeholder="Nama item / menu" value="${escapeHtml(name)}" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-dark small mb-1 fw-semibold">Jumlah (Qty)</label>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-minus stepper-btn" aria-label="Kurang kuantitas"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="items[${itemIndex}][qty]" class="form-control text-center item-qty" value="${qty}" min="1" required>
                            <button type="button" class="btn btn-outline-secondary btn-plus stepper-btn" aria-label="Tambah kuantitas"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label text-dark small mb-1 fw-semibold">Harga Satuan (Rp)</label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted">Rp</span>
                                <input type="text" inputmode="numeric" name="items[${itemIndex}][price]" class="form-control form-control-sm item-price currency-input" placeholder="0" value="${formatThousand(price)}" required>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-row p-2" title="Hapus Item">
                                <i class="fa-solid fa-trash-can fs-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        itemsContainer.appendChild(card);
        itemIndex++;

        const btnMinus = card.querySelector('.btn-minus');
        const btnPlus = card.querySelector('.btn-plus');
        const qtyInput = card.querySelector('.item-qty');
        const priceInput = card.querySelector('.item-price');
        const btnDelete = card.querySelector('.btn-delete-row');

        attachCurrencyFormatter(priceInput);

        btnMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) {
                qtyInput.value = val - 1;
                recalculateTotals();
            }
        });

        btnPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            qtyInput.value = val + 1;
            recalculateTotals();
        });

        qtyInput.addEventListener('input', recalculateTotals);

        btnDelete.addEventListener('click', () => {
            card.remove();
            if (itemsContainer.querySelectorAll('.item-row').length === 0) {
                noItemsState.classList.remove('d-none');
            }
            recalculateTotals();
        });

        recalculateTotals();
    }

    function recalculateTotals() {
        let itemsSubtotal = 0;
        const rows = itemsContainer.querySelectorAll('.item-row');

        rows.forEach(row => {
            const qty = parseInt(row.querySelector('.item-qty').value) || 0;
            const price = parseRawNumber(row.querySelector('.item-price').value);
            itemsSubtotal += (qty * price);
        });

        const deliveryFee = parseRawNumber(inputDeliveryFee.value);
        const serviceFee = parseRawNumber(inputServiceFee.value);
        const discount = parseRawNumber(inputDiscount.value);

        const grandTotal = Math.max(0, itemsSubtotal + deliveryFee + serviceFee - discount);

        displayItemsSubtotal.innerText = formatRupiah(itemsSubtotal);
        displayGrandTotal.innerText = formatRupiah(grandTotal);
    }

    // Confirmation Modal Trigger
    const btnOpenConfirmModal = document.getElementById('btnOpenConfirmModal');
    const btnFinalSubmit = document.getElementById('btnFinalSubmit');
    const confirmBillModalEl = document.getElementById('confirmBillModal');
    const confirmBillModal = confirmBillModalEl ? new bootstrap.Modal(confirmBillModalEl) : null;

    if (btnOpenConfirmModal) {
        btnOpenConfirmModal.addEventListener('click', function() {
            // First check standard HTML form validity (required fields: host_name, title, etc)
            if (!billForm.checkValidity()) {
                billForm.reportValidity();
                return;
            }

            const rows = itemsContainer.querySelectorAll('.item-row');
            if (rows.length === 0) {
                alert('Mohon tambahkan minimal 1 item pesanan sebelum membuat tagihan.');
                return;
            }

            // Extract Host and Title
            const hostName = billForm.querySelector('input[name="host_name"]').value.trim() || '-';
            const title = billForm.querySelector('input[name="title"]').value.trim() || '-';

            document.getElementById('confirmHostName').innerText = hostName;
            document.getElementById('confirmBillTitle').innerText = title;

            // Populate Items list
            const confirmItemsList = document.getElementById('confirmItemsList');
            confirmItemsList.innerHTML = '';
            let itemsSubtotal = 0;
            let itemCount = 0;

            rows.forEach(row => {
                const name = row.querySelector('.item-name').value.trim() || 'Item';
                const qty = parseInt(row.querySelector('.item-qty').value) || 0;
                const price = parseRawNumber(row.querySelector('.item-price').value);
                const lineTotal = qty * price;
                itemsSubtotal += lineTotal;
                itemCount += qty;

                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
                li.innerHTML = `
                    <div>
                        <span class="fw-semibold text-dark">${escapeHtml(name)}</span>
                        <small class="text-muted ms-1">(${qty}x @ ${formatRupiah(price)})</small>
                    </div>
                    <span class="fw-semibold text-dark">${formatRupiah(lineTotal)}</span>
                `;
                confirmItemsList.appendChild(li);
            });

            document.getElementById('confirmItemCount').innerText = itemCount;
            document.getElementById('confirmItemsSubtotal').innerText = formatRupiah(itemsSubtotal);

            // Additional Fees & Discounts
            const deliveryFee = parseRawNumber(inputDeliveryFee.value);
            const serviceFee = parseRawNumber(inputServiceFee.value);
            const discount = parseRawNumber(inputDiscount.value);

            document.getElementById('confirmDeliveryFee').innerText = formatRupiah(deliveryFee);
            document.getElementById('confirmServiceFee').innerText = formatRupiah(serviceFee);
            document.getElementById('confirmDiscount').innerText = '-' + formatRupiah(discount);

            const grandTotal = Math.max(0, itemsSubtotal + deliveryFee + serviceFee - discount);
            document.getElementById('confirmGrandTotal').innerText = formatRupiah(grandTotal);

            // Show Confirmation Modal
            if (confirmBillModal) {
                confirmBillModal.show();
            }
        });
    }

    if (btnFinalSubmit) {
        btnFinalSubmit.addEventListener('click', function() {
            // Strip dots formatting so backend receives clean raw numbers
            document.querySelectorAll('.currency-input').forEach(input => {
                input.value = parseRawNumber(input.value);
            });
            if (toggleBankOption.checked) {
                saveBanksToStorage();
            }
            billForm.submit();
        });
    }

    // On Form Submit fallback
    billForm.addEventListener('submit', function() {
        document.querySelectorAll('.currency-input').forEach(input => {
            input.value = parseRawNumber(input.value);
        });
        if (toggleBankOption.checked) {
            saveBanksToStorage();
        }
    });

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(num);
    }

    function escapeHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    addItemRow('', 1, 0);
});
</script>
@endsection

