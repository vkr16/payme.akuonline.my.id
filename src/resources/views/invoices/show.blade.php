@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number . ' - PayMe')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <!-- ACTION TOOLBAR (Hidden when printed) -->
        <div class="d-print-none mb-4" id="invoiceActionsBar">
            <div class="card glass-card shadow-sm p-3">
                <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
                    <!-- Baris 1 di Mobile: Tombol Buat Invoice & Status Badge -->
                    <div class="d-flex align-items-center justify-content-between justify-content-md-start gap-2">
                        <a href="{{ route('invoices.create') }}" class="btn btn-light btn-sm btn-pill px-3 shadow-xs d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Buat Invoice</span>
                            <span class="badge bg-warning text-dark border border-warning-subtle fw-bold rounded-pill" style="font-size: 0.65rem; padding: 2px 6px;">Beta</span>
                        </a>
                        <span class="badge {{ $invoice->status_badge_class }} px-3 py-2 fs-6 rounded-pill" id="badgeInvoiceStatus">
                            {{ $invoice->status_label }}
                        </span>
                    </div>

                    <!-- Baris 2 di Mobile: Action Buttons Terdistribusi Rapi -->
                    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between justify-content-md-end">
                        <!-- Print / PDF -->
                        <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-2 px-sm-3 shadow-xs flex-fill flex-md-grow-0" onclick="window.print()">
                            <i class="fa-solid fa-print me-1"></i> <span class="d-inline">Cetak PDF</span>
                        </button>

                        <!-- Share WhatsApp -->
                        <a href="{{ $invoice->whatsapp_share_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm btn-pill px-2 px-sm-3 shadow-xs text-white flex-fill flex-md-grow-0">
                            <i class="fa-brands fa-whatsapp me-1"></i> <span class="d-inline">Kirim WA</span>
                        </a>

                        <!-- Copy Link -->
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-pill px-2 px-sm-3 shadow-xs flex-fill flex-md-grow-0" id="btnCopyLink">
                            <i class="fa-solid fa-link me-1"></i> <span id="copyLinkText">Salin Link</span>
                        </button>

                        <!-- Change Status Button (Verified by Passcode) -->
                        <button type="button" class="btn btn-gradient-primary btn-sm btn-pill px-3 shadow-xs d-flex align-items-center justify-content-center gap-1 flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                            <i class="fa-solid fa-key me-1"></i>
                            <span>Ubah Status</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 1: KATA SANDI BARU (DITAMPILKAN SEKALI SAAT INVOICE BARU DIBUAT) -->
        @if($newPasscode)
            <div class="modal fade d-print-none" id="newPasscodeModal" tabindex="-1" aria-labelledby="newPasscodeModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                    <i class="fa-solid fa-key fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold text-dark fs-6" id="newPasscodeModalLabel">Kata Sandi Pengelolaan Invoice</h5>
                                    <span class="text-muted small">Simpan atau unduh kata sandi ini sekarang</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body py-3">
                            <div class="alert alert-warning border-0 rounded-3 small mb-3">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Kata sandi ini digunakan untuk <strong>mengubah status invoice</strong> menjadi <strong>Sudah Dibayar (LUNAS)</strong> atau <strong>Batal</strong> sewaktu-waktu. Harap simpan dengan baik.
                            </div>

                            <label class="form-label text-secondary small fw-semibold mb-1">Kata Sandi Invoice Anda:</label>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 border border-secondary-subtle mb-3">
                                <span class="fs-3 fw-extrabold font-monospace text-primary tracking-wider" id="newPasscodeDisplay">{{ $newPasscode }}</span>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs" id="btnCopyNewPasscode">
                                    <i class="fa-regular fa-copy me-1"></i> <span id="copyNewPasscodeText">Salin</span>
                                </button>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-dark btn-pill py-2 shadow-xs d-flex align-items-center justify-content-center gap-2" id="btnDownloadPasscodeTxt">
                                    <i class="fa-solid fa-download"></i>
                                    <span>Download Kata Sandi (.TXT)</span>
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-primary btn-pill w-100 py-2 fw-semibold" data-bs-dismiss="modal">
                                Saya Sudah Menyimpan Kata Sandi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 2: UBAH STATUS INVOICE (DENGAN KATA SANDI) -->
        <div class="modal fade d-print-none" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom border-light-subtle pb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="fa-solid fa-sliders fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark fs-6" id="changeStatusModalLabel">Ubah Status Invoice</h5>
                                <span class="text-muted small">Status saat ini: <strong class="text-dark">{{ $invoice->status_label }}</strong></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div id="changeStatusAlert" class="alert alert-danger d-none border-0 rounded-3 small mb-3"></div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold mb-1">Pilih Status Baru <span class="text-danger">*</span></label>
                            <select id="modalSelectStatus" class="form-select shadow-xs">
                                <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Sudah Dibayar (LUNAS)</option>
                                <option value="unpaid" {{ $invoice->status === 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="cancelled" {{ $invoice->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold mb-1">Kata Sandi Invoice <span class="text-danger">*</span></label>
                            <div class="input-group shadow-xs">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                <input type="text" id="modalInputPasscode" class="form-control border-start-0 ps-0 font-monospace text-uppercase" placeholder="Contoh: {{ $invoice->passcode ? '8 Karakter' : 'Kata Sandi' }}" maxlength="32" autocomplete="off">
                            </div>
                            <div class="form-text small" style="font-size: 0.75rem;">Masukkan kata sandi yang digenerate saat invoice pertama kali dibuat.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light btn-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-gradient-primary btn-pill px-4 fw-semibold shadow-xs" id="btnSubmitStatusWithPasscode">
                            <span id="submitStatusBtnText">Simpan Perubahan</span>
                            <span id="submitStatusBtnSpinner" class="spinner-border spinner-border-sm d-none ms-1" role="status"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- INVOICE PAPER DOCUMENT (Pure White Background) -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5 bg-white" id="invoiceDocument">
            <div class="p-4 p-md-5">

                <!-- 1. Header: Business Logo & Invoice Meta -->
                <div class="invoice-section-header pb-4 mb-4 border-bottom border-light-subtle">
                    <div class="row align-items-start justify-content-between g-4">
                        <div class="col-sm-7">
                            @if($invoice->sender_logo_path)
                                <div class="mb-3">
                                    <img src="{{ route('invoices.logo', $invoice->slug) }}" alt="{{ $invoice->sender_name }}" class="img-fluid rounded-3" style="max-height: 75px; max-width: 240px; object-fit: contain;">
                                </div>
                            @endif
                            <h2 class="fw-bold text-dark fs-4 mb-1">{{ $invoice->sender_name }}</h2>
                            @if($invoice->sender_address)
                                <p class="text-muted small mb-1" style="white-space: pre-line;">{{ $invoice->sender_address }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-3 text-muted small mt-2">
                                @if($invoice->sender_phone)
                                    <span><i class="fa-solid fa-phone fa-xs me-1"></i> {{ $invoice->sender_phone }}</span>
                                @endif
                                @if($invoice->sender_email)
                                    <span><i class="fa-solid fa-envelope fa-xs me-1"></i> {{ $invoice->sender_email }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-5 text-sm-end">
                            <div class="text-uppercase fw-extrabold text-primary fs-3 tracking-wide mb-1" style="letter-spacing: 2px;">
                                INVOICE
                            </div>
                            <div class="fw-bold text-dark fs-5 mb-2">{{ $invoice->invoice_number }}</div>

                            <!-- Status Badge -->
                            <div class="mb-3">
                                <span class="badge {{ $invoice->status_badge_class }} px-3 py-2 rounded-pill fs-6 fw-bold">
                                    {{ strtoupper($invoice->status_label) }}
                                </span>
                            </div>

                            <div class="text-muted small">
                                <div><strong>Tanggal Tagihan:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-' }}</div>
                                @if($invoice->due_date)
                                    <div><strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d/m/Y') }}</div>
                                @endif
                                @if($invoice->paid_at)
                                    <div class="text-success mt-1"><strong>Dibayar pada:</strong> {{ $invoice->paid_at->format('d/m/Y H:i') }} WIB</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Parties: Recipient Info (Tagihan Kepada) -->
                <div class="invoice-section-parties mb-4 pb-2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-muted text-uppercase small fw-bold mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Tagihan Kepada:</div>
                            <h5 class="fw-bold text-dark mb-1 fs-6">{{ $invoice->client_name }}</h5>
                            @if($invoice->client_company)
                                <div class="fw-semibold text-secondary small mb-1">{{ $invoice->client_company }}</div>
                            @endif
                            @if($invoice->client_address)
                                <p class="text-muted small mb-1" style="white-space: pre-line;">{{ $invoice->client_address }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                                @if($invoice->client_phone)
                                    <span><i class="fa-solid fa-phone fa-xs me-1"></i> {{ $invoice->client_phone }}</span>
                                @endif
                                @if($invoice->client_email)
                                    <span><i class="fa-solid fa-envelope fa-xs me-1"></i> {{ $invoice->client_email }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Line Items Section (Responsive: Cards on Mobile, Table on Desktop & Print) -->
                <div class="invoice-section-items mb-4">
                    <!-- Desktop & Print View (Table) -->
                    <div class="d-none d-md-block d-print-block table-responsive">
                        <table class="table table-bordered border-light-subtle align-middle mb-0">
                            <thead class="bg-white border-bottom border-2 border-secondary-subtle">
                                <tr class="text-secondary small fw-bold text-uppercase" style="font-size: 0.75rem;">
                                    <th style="width: 50px;" class="text-center bg-white">#</th>
                                    <th class="bg-white">Deskripsi Barang / Jasa</th>
                                    <th style="width: 90px;" class="text-center bg-white">Qty</th>
                                    <th style="width: 160px;" class="text-end bg-white">Harga Satuan</th>
                                    <th style="width: 170px;" class="text-end bg-white">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted small bg-white">{{ $index + 1 }}</td>
                                        <td class="bg-white">
                                            <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                        </td>
                                        <td class="text-center bg-white">{{ (float) $item->quantity == (int) $item->quantity ? (int) $item->quantity : $item->quantity }}</td>
                                        <td class="text-end text-muted bg-white">{{ $item->formatted_unit_price }}</td>
                                        <td class="text-end fw-bold text-dark bg-white">{{ $item->formatted_total_price }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View (Clean Stacked Card List, d-none on md & print) -->
                    <div class="d-md-none d-print-none vstack gap-2">
                        <div class="text-secondary small fw-bold text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Rincian Item ({{ count($invoice->items) }})
                        </div>
                        @foreach($invoice->items as $index => $item)
                            <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-xs">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="badge bg-light text-secondary rounded-pill border border-light-subtle px-2 py-1" style="font-size: 0.75rem;">
                                            #{{ $index + 1 }}
                                        </span>
                                        <div class="fw-bold text-dark lh-sm" style="font-size: 0.95rem;">
                                            {{ $item->item_name }}
                                        </div>
                                    </div>
                                    <div class="text-end fw-bold text-dark" style="font-size: 0.95rem;">
                                        {{ $item->formatted_total_price }}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center text-secondary small pt-1 border-top border-light-subtle" style="font-size: 0.8rem;">
                                    <span>
                                        <i class="bi bi-box me-1 text-muted"></i>
                                        {{ (float) $item->quantity == (int) $item->quantity ? (int) $item->quantity : $item->quantity }} pcs
                                        &times; {{ $item->formatted_unit_price }}
                                    </span>
                                    <span class="text-muted">Total</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Totals & Calculations Section (Pure White Container) -->
                <div class="invoice-section-summary row justify-content-end mb-4">
                    <div class="col-12 col-sm-8 col-md-5">
                        <div class="bg-white rounded-4 p-3 border border-light-subtle shadow-xs">
                            <table class="table table-sm table-borderless mb-0 align-middle">
                                <tbody>
                                    <tr>
                                        <td class="text-secondary small py-1 bg-white">Subtotal</td>
                                        <td class="text-end fw-semibold text-dark py-1 bg-white">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                                    </tr>

                                    @if($invoice->discount_amount > 0)
                                        <tr>
                                            <td class="text-secondary small py-1 bg-white">
                                                Diskon
                                                @if($invoice->discount_type === 'percent')
                                                    ({{ (float) $invoice->discount_value }}%)
                                                @endif
                                            </td>
                                            <td class="text-end text-danger fw-semibold py-1 bg-white">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif

                                    @if($invoice->tax_amount > 0)
                                        <tr>
                                            <td class="text-secondary small py-1 bg-white">Pajak / PPN ({{ (float) $invoice->tax_rate }}%)</td>
                                            <td class="text-end text-secondary fw-semibold py-1 bg-white">+ Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif

                                    @if($invoice->shipping_fee > 0)
                                        <tr>
                                            <td class="text-secondary small py-1 bg-white">Ongkir / Biaya Tambahan</td>
                                            <td class="text-end text-secondary fw-semibold py-1 bg-white">+ Rp {{ number_format($invoice->shipping_fee, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif

                                    <tr class="border-top border-light-subtle">
                                        <td class="fw-bold text-dark pt-3 pb-1 fs-6 bg-white">Total Tagihan</td>
                                        <td class="text-end fw-extrabold text-primary pt-3 pb-1 fs-5 bg-white">{{ $invoice->formatted_total_amount }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. Payment Details (Multi Bank & Dynamic QRIS - Pure White) -->
                @php
                    $hasBanks = $invoice->banks->count() > 0 || $invoice->bank_name;
                    $hasQris = !empty($dynamicQrisPayload) || !empty($invoice->qris_static_payload) || !empty($invoice->qris_image_path);
                @endphp

                @if($hasBanks || $hasQris)
                    <div class="invoice-section-payment p-4 rounded-4 bg-white border border-light-subtle mb-4">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 small text-uppercase" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-credit-card text-primary"></i>
                            <span>Instruksi & Metode Pembayaran</span>
                        </h6>

                        <div class="row g-4 align-items-start">
                            <!-- Multi Bank Accounts -->
                            @if($hasBanks)
                                <div class="{{ $hasQris ? 'col-md-7' : 'col-12' }}">
                                    <div class="vstack gap-3">
                                        @if($invoice->banks->count() > 0)
                                            @foreach($invoice->banks as $bank)
                                                <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-xs">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="fw-bold text-dark fs-6">{{ $bank->bank_name }}</span>
                                                        @if($bank->account_holder)
                                                            <span class="text-muted small">a.n. {{ $bank->account_holder }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                                                        <span class="fs-5 fw-extrabold text-dark font-monospace text-primary">
                                                            {{ $bank->account_number }}
                                                        </span>
                                                        <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs btn-copy-acc d-print-none" data-acc="{{ $bank->account_number }}">
                                                            <i class="fa-regular fa-copy me-1"></i> Salin
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @elseif($invoice->bank_name && $invoice->bank_account_number)
                                            <div class="p-3 bg-white rounded-3 border border-light-subtle shadow-xs">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-dark fs-6">{{ $invoice->bank_name }}</span>
                                                    @if($invoice->bank_account_holder)
                                                        <span class="text-muted small">a.n. {{ $invoice->bank_account_holder }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                                                    <span class="fs-5 fw-extrabold text-dark font-monospace text-primary">
                                                        {{ $invoice->bank_account_number }}
                                                    </span>
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs btn-copy-acc d-print-none" data-acc="{{ $invoice->bank_account_number }}">
                                                        <i class="fa-regular fa-copy me-1"></i> Salin
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Dynamic QRIS Display -->
                            @if($hasQris)
                                <div class="{{ $hasBanks ? 'col-md-5' : 'col-12' }} text-center text-md-end">
                                    <div class="d-inline-block text-center p-3 bg-white rounded-4 shadow-xs border border-light-subtle" style="min-width: 250px; max-width: 320px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small fw-bold" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-bolt text-warning me-1"></i> QRIS DINAMIS
                                            </span>
                                        </div>

                                        @if(!empty($merchantInfo['merchant_name']))
                                            <div class="fw-bold text-dark fs-6 mt-1 mb-0">{{ $merchantInfo['merchant_name'] }}</div>
                                            @if(!empty($merchantInfo['merchant_city']))
                                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $merchantInfo['merchant_city'] }}</div>
                                            @endif
                                        @endif

                                        <!-- Dynamic QR Canvas Container -->
                                        <div id="dynamicQrisContainer" class="d-flex justify-content-center align-items-center my-2 p-1" style="min-height: 200px;">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        </div>

                                        <div class="bg-white p-2 rounded-3 text-center my-2 border border-light-subtle">
                                            <div class="text-muted small" style="font-size: 0.72rem;">Nominal Terkunci:</div>
                                            <div class="fw-extrabold text-primary fs-5">{{ $invoice->formatted_total_amount }}</div>
                                        </div>

                                        <div class="d-flex gap-2 justify-content-center d-print-none mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 shadow-xs" id="btnDownloadQris">
                                                <i class="fa-solid fa-download me-1"></i> Unduh QR
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn-pill px-3 shadow-xs" id="btnCopyQrisPayload">
                                                <i class="fa-solid fa-copy me-1"></i> <span id="copyQrisText">Salin Kode</span>
                                            </button>
                                        </div>

                                        <div class="text-muted small mt-2 d-print-none" style="font-size: 0.7rem;">
                                            Bisa di-scan via BCA, Livin, BRImo, GoPay, Dana, OVO, ShopeePay.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 6. Notes & Terms -->
                @if($invoice->notes || $invoice->terms)
                    <div class="invoice-section-notes row g-3 pt-2 text-muted small mb-4">
                        @if($invoice->notes)
                            <div class="col-md-6">
                                <div class="fw-bold text-dark mb-1 text-uppercase" style="font-size: 0.72rem;">Catatan:</div>
                                <p class="mb-0" style="white-space: pre-line;">{{ $invoice->notes }}</p>
                            </div>
                        @endif

                        @if($invoice->terms)
                            <div class="col-md-6">
                                <div class="fw-bold text-dark mb-1 text-uppercase" style="font-size: 0.72rem;">Syarat & Ketentuan:</div>
                                <p class="mb-0" style="white-space: pre-line;">{{ $invoice->terms }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- 7. Footer Watermark -->
                <div class="invoice-section-footer pt-4 text-center text-muted small border-top border-light-subtle" style="font-size: 0.75rem;">
                    Dibuat secara otomatis melalui <strong>PayMe Invoice Maker</strong> (payme.akuonline.my.id)
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
/* Optimized Print Styles for A4 Paper with Clean Margins */
@media print {
    html, body {
        background: #ffffff !important;
        background-image: none !important;
        color: #0f172a !important;
        margin: 0 !important;
        padding: 0 !important;
        min-height: auto !important;
        font-size: 13px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Completely hide navigation, action toolbars, footer, modals, and buttons */
    nav.navbar,
    footer,
    #invoiceActionsBar,
    .d-print-none,
    .modal,
    .modal-backdrop,
    .alert,
    .btn,
    .dropdown-menu {
        display: none !important;
        visibility: hidden !important;
    }

    main.container,
    .container {
        max-width: 100% !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .row, .col-lg-10, .col-xl-9 {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    #invoiceDocument {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        background: #ffffff !important;
    }

    #invoiceDocument .card-body,
    #invoiceDocument > div {
        padding: 0 !important;
    }

    /* Page-level layout */
    @page {
        size: A4 portrait;
        margin: 15mm 18mm;
    }

    /* Section-level spacing & margins for clean A4 printing */
    .invoice-section-header {
        margin-bottom: 24px !important;
        padding-bottom: 20px !important;
        border-bottom: 1px solid #cbd5e1 !important;
    }

    .invoice-section-parties {
        margin-bottom: 24px !important;
        page-break-inside: avoid;
    }

    .invoice-section-items {
        margin-bottom: 24px !important;
    }

    .invoice-section-items table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .invoice-section-items table th,
    .invoice-section-items table td {
        border: 1px solid #cbd5e1 !important;
        padding: 9px 12px !important;
        background-color: #ffffff !important;
    }

    .invoice-section-items table th {
        font-weight: 700 !important;
        color: #334155 !important;
        background-color: #f8fafc !important;
    }

    .invoice-section-summary {
        margin-top: 10px !important;
        margin-bottom: 28px !important;
        page-break-inside: avoid;
    }

    .invoice-section-summary .bg-white {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 12px 16px !important;
    }

    .invoice-section-payment {
        margin-top: 20px !important;
        margin-bottom: 24px !important;
        padding: 18px 20px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        page-break-inside: avoid;
    }

    .invoice-section-notes {
        margin-top: 16px !important;
        margin-bottom: 24px !important;
        page-break-inside: avoid;
    }

    .invoice-section-footer {
        margin-top: 32px !important;
        padding-top: 16px !important;
        border-top: 1px solid #cbd5e1 !important;
        page-break-inside: avoid;
    }

    /* Force background colors to stay pure white */
    .bg-light,
    .bg-white,
    .card {
        background: #ffffff !important;
        background-color: #ffffff !important;
    }

    /* Remove URL print annotations */
    a[href]:after {
        content: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slug = @json($invoice->slug);
    const newPasscode = @json($newPasscode);
    const dynamicQrisPayload = @json($dynamicQrisPayload);

    // Key for storing passcode locally on creator's machine
    const PASSCODE_STORAGE_KEY = 'payme_invoice_passcode_' + slug;
    if (newPasscode) {
        try {
            localStorage.setItem(PASSCODE_STORAGE_KEY, newPasscode);
        } catch (e) {}
    }

    // Auto-open new passcode modal if present
    const newPasscodeModalEl = document.getElementById('newPasscodeModal');
    if (newPasscodeModalEl && typeof bootstrap !== 'undefined') {
        const modalInstance = new bootstrap.Modal(newPasscodeModalEl);
        modalInstance.show();
    }

    // Download Passcode as .TXT file
    function downloadPasscodeTxt() {
        const invoiceNumber = @json($invoice->invoice_number);
        const invoiceDate = @json($invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '-');
        const clientName = @json($invoice->client_name);
        const totalAmount = @json($invoice->formatted_total_amount);
        const code = newPasscode || (localStorage.getItem(PASSCODE_STORAGE_KEY) || '');
        const invoiceUrl = window.location.origin + window.location.pathname;

        const textContent = `================================================
KATA SANDI PENGELOLAAN INVOICE - PAYME
================================================
Nomor Invoice : ${invoiceNumber}
Tanggal       : ${invoiceDate}
Klien         : ${clientName}
Total Tagihan : ${totalAmount}
Link Invoice  : ${invoiceUrl}

KATA SANDI    : ${code}
================================================
PENTING:
Gunakan kata sandi di atas untuk mengubah status
invoice menjadi "SUDAH DIBAYAR" (LUNAS) atau "BATAL"
kapan saja di halaman detail invoice.

Harap simpan file ini dengan baik!
================================================`;

        const blob = new Blob([textContent], { type: 'text/plain;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `kata-sandi-${invoiceNumber}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    const btnDownloadPasscodeTxt = document.getElementById('btnDownloadPasscodeTxt');
    if (btnDownloadPasscodeTxt) {
        btnDownloadPasscodeTxt.addEventListener('click', downloadPasscodeTxt);
    }

    // Copy new passcode in modal
    const btnCopyNewPasscode = document.getElementById('btnCopyNewPasscode');
    const copyNewPasscodeText = document.getElementById('copyNewPasscodeText');
    if (btnCopyNewPasscode && newPasscode) {
        btnCopyNewPasscode.addEventListener('click', function () {
            copyToClipboard(newPasscode, function () {
                if (copyNewPasscodeText) copyNewPasscodeText.textContent = 'Tersalin!';
                btnCopyNewPasscode.classList.remove('btn-outline-primary');
                btnCopyNewPasscode.classList.add('btn-primary', 'text-white');
                setTimeout(() => {
                    if (copyNewPasscodeText) copyNewPasscodeText.textContent = 'Salin';
                    btnCopyNewPasscode.classList.remove('btn-primary', 'text-white');
                    btnCopyNewPasscode.classList.add('btn-outline-primary');
                }, 2000);
            });
        });
    }

    // Pre-fill passcode in changeStatusModal if stored locally
    const changeStatusModalEl = document.getElementById('changeStatusModal');
    const modalInputPasscode = document.getElementById('modalInputPasscode');
    if (changeStatusModalEl && modalInputPasscode) {
        changeStatusModalEl.addEventListener('show.bs.modal', function () {
            const savedPass = localStorage.getItem(PASSCODE_STORAGE_KEY);
            if (savedPass && !modalInputPasscode.value) {
                modalInputPasscode.value = savedPass;
            }
        });
    }

    // Submit Status Change with Passcode
    const btnSubmitStatusWithPasscode = document.getElementById('btnSubmitStatusWithPasscode');
    const modalSelectStatus = document.getElementById('modalSelectStatus');
    const changeStatusAlert = document.getElementById('changeStatusAlert');
    const submitStatusBtnText = document.getElementById('submitStatusBtnText');
    const submitStatusBtnSpinner = document.getElementById('submitStatusBtnSpinner');

    if (btnSubmitStatusWithPasscode) {
        btnSubmitStatusWithPasscode.addEventListener('click', function () {
            const status = modalSelectStatus ? modalSelectStatus.value : 'paid';
            const passcode = modalInputPasscode ? modalInputPasscode.value.trim() : '';

            if (!passcode) {
                if (changeStatusAlert) {
                    changeStatusAlert.textContent = 'Silakan masukkan kata sandi invoice.';
                    changeStatusAlert.classList.remove('d-none');
                }
                if (modalInputPasscode) modalInputPasscode.focus();
                return;
            }

            if (changeStatusAlert) changeStatusAlert.classList.add('d-none');
            if (submitStatusBtnText) submitStatusBtnText.textContent = 'Memverifikasi...';
            if (submitStatusBtnSpinner) submitStatusBtnSpinner.classList.remove('d-none');
            btnSubmitStatusWithPasscode.disabled = true;

            fetch('{{ route("invoices.status", $invoice->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    status: status,
                    passcode: passcode,
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    try {
                        localStorage.setItem(PASSCODE_STORAGE_KEY, passcode);
                    } catch (e) {}
                    location.reload();
                } else {
                    if (changeStatusAlert) {
                        changeStatusAlert.textContent = data.message || 'Kata sandi salah. Silakan coba lagi.';
                        changeStatusAlert.classList.remove('d-none');
                    }
                    if (submitStatusBtnText) submitStatusBtnText.textContent = 'Simpan Perubahan';
                    if (submitStatusBtnSpinner) submitStatusBtnSpinner.classList.add('d-none');
                    btnSubmitStatusWithPasscode.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                if (changeStatusAlert) {
                    changeStatusAlert.textContent = 'Terjadi kesalahan koneksi server.';
                    changeStatusAlert.classList.remove('d-none');
                }
                if (submitStatusBtnText) submitStatusBtnText.textContent = 'Simpan Perubahan';
                if (submitStatusBtnSpinner) submitStatusBtnSpinner.classList.add('d-none');
                btnSubmitStatusWithPasscode.disabled = false;
            });
        });
    }

    /**
     * Render Dynamic QR code with white margin (quiet zone) baked in
     */
    function renderQrWithQuietZone(container, payload, options = {}) {
        if (!container || !payload) return;
        const qrSize = options.qrSize || 280;
        const margin = options.margin !== undefined ? options.margin : 20;
        const displaySize = options.displaySize || '200px';

        container.innerHTML = '';

        if (typeof QRCode === 'undefined') {
            container.innerHTML = '<span class="text-danger small">Library QRCode belum siap</span>';
            return;
        }

        const tempDiv = document.createElement('div');
        new QRCode(tempDiv, {
            text: payload,
            width: qrSize,
            height: qrSize,
            correctLevel: QRCode.CorrectLevel.M
        });

        const rawCanvas = tempDiv.querySelector('canvas');
        const rawImg = tempDiv.querySelector('img');
        const rawSource = rawCanvas || rawImg;

        if (!rawSource) return;

        const totalSize = qrSize + (margin * 2);
        const finalCanvas = document.createElement('canvas');
        finalCanvas.width = totalSize;
        finalCanvas.height = totalSize;
        const ctx = finalCanvas.getContext('2d');

        // Fill pure white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, totalSize, totalSize);

        // Draw QR code in center
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(rawSource, margin, margin, qrSize, qrSize);

        const finalImg = document.createElement('img');
        finalImg.src = finalCanvas.toDataURL('image/png');
        finalImg.alt = 'QRIS Dinamis';
        finalImg.className = 'img-fluid d-block mx-auto rounded-3';
        finalImg.style.maxWidth = displaySize;
        finalImg.style.width = '100%';
        finalImg.style.height = 'auto';

        container.appendChild(finalImg);
    }

    // Render Dynamic QRIS
    const dynamicQrisContainer = document.getElementById('dynamicQrisContainer');
    if (dynamicQrisPayload && dynamicQrisContainer) {
        renderQrWithQuietZone(dynamicQrisContainer, dynamicQrisPayload, {
            qrSize: 280,
            margin: 20,
            displaySize: '200px'
        });
    }

    // Universal Robust Clipboard Copy Function
    function copyToClipboard(text, successCallback) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                if (successCallback) successCallback();
            }).catch(() => {
                fallbackCopy(text, successCallback);
            });
        } else {
            fallbackCopy(text, successCallback);
        }
    }

    function fallbackCopy(text, successCallback) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            if (successCallback) successCallback();
        } catch (err) {
            prompt('Salin teks secara manual:', text);
        }
        document.body.removeChild(textArea);
    }

    // Copy Invoice Link
    const btnCopyLink = document.getElementById('btnCopyLink');
    const copyLinkText = document.getElementById('copyLinkText');
    if (btnCopyLink) {
        btnCopyLink.addEventListener('click', function () {
            const publicUrl = window.location.origin + window.location.pathname;
            copyToClipboard(publicUrl, function () {
                if (copyLinkText) copyLinkText.textContent = 'Tersalin!';
                btnCopyLink.classList.remove('btn-outline-secondary');
                btnCopyLink.classList.add('btn-secondary');
                setTimeout(() => {
                    if (copyLinkText) copyLinkText.textContent = 'Salin Tautan';
                    btnCopyLink.classList.remove('btn-secondary');
                    btnCopyLink.classList.add('btn-outline-secondary');
                }, 2000);
            });
        });
    }

    // Copy Account Numbers
    document.querySelectorAll('.btn-copy-acc').forEach(btn => {
        btn.addEventListener('click', function () {
            const acc = this.getAttribute('data-acc') || this.dataset.acc;
            if (!acc) return;
            const originalText = this.innerHTML;
            const btnEl = this;
            copyToClipboard(acc, function () {
                btnEl.innerHTML = '<i class="fa-solid fa-check me-1"></i> Tersalin!';
                btnEl.classList.remove('btn-outline-primary');
                btnEl.classList.add('btn-primary', 'text-white');
                setTimeout(() => {
                    btnEl.innerHTML = originalText;
                    btnEl.classList.remove('btn-primary', 'text-white');
                    btnEl.classList.add('btn-outline-primary');
                }, 2000);
            });
        });
    });

    // Download Dynamic QR Code
    const btnDownloadQris = document.getElementById('btnDownloadQris');
    if (btnDownloadQris) {
        btnDownloadQris.addEventListener('click', function () {
            const img = document.querySelector('#dynamicQrisContainer img');
            if (img && img.src) {
                const a = document.createElement('a');
                a.href = img.src;
                a.download = 'QRIS-' + slug + '.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert('Gambar QR belum siap.');
            }
        });
    }

    // Copy Dynamic QRIS Payload
    const btnCopyQrisPayload = document.getElementById('btnCopyQrisPayload');
    const copyQrisText = document.getElementById('copyQrisText');
    if (btnCopyQrisPayload && dynamicQrisPayload) {
        btnCopyQrisPayload.addEventListener('click', function () {
            copyToClipboard(dynamicQrisPayload, function () {
                if (copyQrisText) copyQrisText.textContent = 'Tersalin!';
                btnCopyQrisPayload.classList.remove('btn-outline-secondary');
                btnCopyQrisPayload.classList.add('btn-secondary');
                setTimeout(() => {
                    if (copyQrisText) copyQrisText.textContent = 'Salin Kode';
                    btnCopyQrisPayload.classList.remove('btn-secondary');
                    btnCopyQrisPayload.classList.add('btn-outline-secondary');
                }, 2000);
            });
        });
    }
});
</script>
@endpush
