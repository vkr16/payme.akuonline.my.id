@extends('layouts.app')

@section('title', $bill->title . ' - PayMe Split Bill')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Header Info Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 text-center">
                <span class="badge bg-primary mb-2 px-3 py-2 fs-6">
                    <i class="fa-solid fa-user-tag me-1"></i> Ditalangin oleh: <strong>{{ $bill->host_name }}</strong>
                </span>

                <h2 class="fw-bold text-dark mb-2 fs-3">{{ $bill->title }}</h2>

                @if($bill->qris_merchant_name)
                    <p class="text-muted small mb-3">
                        <i class="fa-solid fa-store me-1 text-primary"></i> Merchant: <strong>{{ $bill->qris_merchant_name }}</strong>
                        @if($bill->qris_merchant_city) ({{ $bill->qris_merchant_city }}) @endif
                    </p>
                @endif

                <!-- Progress Bar Patungan -->
                <div class="p-3 rounded bg-light border mb-3 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted fw-semibold"><i class="fa-solid fa-chart-pie me-1 text-primary"></i> Progress Terkumpul</span>
                        <span class="fw-bold text-success">
                            Rp {{ number_format($bill->total_paid, 0, ',', '.') }} / Rp {{ number_format($bill->total_amount, 0, ',', '.') }} ({{ $bill->payment_progress_percentage }}%)
                        </span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $bill->payment_progress_percentage }}%;" aria-valuenow="{{ $bill->payment_progress_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                @php
                    $shareUrl = route('bills.show', ['slug' => $bill->slug]);
                    if (str_contains($shareUrl, ':///') || !str_contains($shareUrl, '://')) {
                        $baseUrl = rtrim(config('app.url', 'https://payme.akuonline.my.id'), '/');
                        $shareUrl = $baseUrl . '/b/' . $bill->slug;
                    }
                @endphp

                <!-- Share Link & Receipt Action Box -->
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-2">
                    @if($bill->receipt_image_path)
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#receiptModal">
                            <i class="fa-solid fa-file-invoice me-1"></i> Lihat Struk Asli
                        </button>
                    @endif
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnCopyShareLink">
                        <i class="fa-solid fa-link me-1"></i> Salin Link Patungan
                    </button>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('Yuk bayar patungan ' . $bill->title . ' ditalangin ' . $bill->host_name . ' lewat link ini: ' . $shareUrl) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="fa-brands fa-whatsapp me-1"></i> Bagikan WA
                    </a>
                </div>

                <!-- Bank Info Box if available -->
                @php
                    $allBanks = $bill->banks->count() > 0 ? $bill->banks : collect([
                        (object)[
                            'bank_name' => $bill->bank_name,
                            'account_number' => $bill->bank_account_number,
                            'account_holder' => $bill->bank_account_holder,
                        ]
                    ])->filter(fn($b) => !empty($b->bank_name) && !empty($b->account_number));
                @endphp

                @if($allBanks->count() > 0)
                    <div class="p-3 rounded-3 bg-light border text-start mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-3">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-2.5 py-1.5 fw-semibold">
                                <i class="fa-solid fa-building-columns text-warning me-1"></i> Opsi Transfer Bank & Dompet Digital ({{ $allBanks->count() }})
                            </span>
                            <small class="text-muted">Salin nomor rekening untuk transfer manual</small>
                        </div>

                        <div class="vstack gap-2">
                            @foreach($allBanks as $bank)
                                <div class="p-3 rounded-3 bg-white border shadow-xs">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small fw-bold">
                                                    {{ $bank->bank_name }}
                                                </span>
                                                <span class="fw-bold text-dark fs-6 user-select-all font-monospace">
                                                    {{ $bank->account_number }}
                                                </span>
                                            </div>
                                            <div class="text-muted small">
                                                <span>Atas Nama:</span> <strong class="text-dark">{{ $bank->account_holder ?: '-' }}</strong>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 btn-copy-bank d-inline-flex align-items-center gap-1 shadow-xs" data-acc="{{ $bank->account_number }}" data-bank="{{ $bank->bank_name }}">
                                                <i class="fa-solid fa-copy"></i>
                                                <span>Salin Rekening</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Participant Item Selection -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-utensils text-primary"></i>
                    <span>Pilih Menu Pesanan Kamu</span>
                </h5>
                <small class="text-muted d-block mt-1">Tentukan porsi atau item yang ingin kamu bayar</small>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="vstack gap-3" id="participantItemsList">
                    @foreach($bill->items as $item)
                        @php
                            $remaining = $item->remaining_qty;
                        @endphp
                        <div class="p-3 rounded-3 bg-white border item-claim-card {{ $remaining === 0 ? 'bg-light opacity-75' : '' }}" data-item-id="{{ $item->id }}" data-item-price="{{ $item->price }}" data-item-max="{{ $remaining }}" data-item-name="{{ $item->name }}">
                            <!-- Top row: Item Name, Status badge, and Unit Price info -->
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-1 fs-6">{{ $item->name }}</h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                                        <span class="fw-semibold text-dark">Rp {{ number_format($item->price, 0, ',', '.') }} / item</span>
                                        <span>&bull;</span>
                                        @if($remaining > 0)
                                            <span class="text-primary fw-medium">Tersisa: <strong>{{ $remaining }}</strong> dari {{ $item->qty }}</span>
                                        @else
                                            <span class="text-muted">Semua {{ $item->qty }} item sudah lunas</span>
                                        @endif
                                    </div>
                                </div>
                                @if($remaining === 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small flex-shrink-0">
                                        <i class="fa-solid fa-circle-check me-1"></i> Lunas ({{ $item->qty }}/{{ $item->qty }})
                                    </span>
                                @endif
                            </div>

                            <!-- Bottom row: Quantity input stepper (always below text) & calculated subtotal -->
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2">
                                <div>
                                    @if($remaining > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small d-none d-sm-inline">Jumlah kamu:</span>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button" class="btn btn-outline-secondary btn-claim-minus stepper-btn" aria-label="Kurang kuantitas"><i class="fa-solid fa-minus"></i></button>
                                                <input type="number" class="form-control text-center claim-qty fw-bold" value="0" min="0" max="{{ $remaining }}" readonly>
                                                <button type="button" class="btn btn-outline-secondary btn-claim-plus stepper-btn" aria-label="Tambah kuantitas"><i class="fa-solid fa-plus"></i></button>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2">Semua Lunas</span>
                                    @endif
                                </div>

                                <div class="text-end">
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal Item</small>
                                    <span class="fw-bold text-dark item-claim-total fs-6">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Bill Summary & Action Buttons -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3 fs-6">Ringkasan Tagihan Saya</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Subtotal Item Saya:</span>
                    <span class="fw-semibold text-dark" id="summaryMyItemsSubtotal">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Proporsi Ongkir & Fee:</span>
                    <span class="fw-semibold text-primary" id="summaryMyFeeShare">Rp 0</span>
                </div>

                <!-- OPSI BULATKAN KE ATAS (TIP/TERIMA KASIH) -->
                <div class="p-2 px-3 bg-light rounded border mb-3">
                    <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="toggleRoundUp" style="cursor: pointer;">
                            <div>
                                <label class="form-check-label fw-semibold text-dark small cursor-pointer" for="toggleRoundUp">
                                    <i class="fa-solid fa-arrow-trend-up text-primary me-1"></i> Bulatkan ke atas ke ribuan terdekat
                                </label>
                                <small class="text-muted d-block" id="roundUpHelperText" style="font-size: 0.73rem;">
                                    Lebihkan sedikit nominal transfer sebagai tanda terima kasih / tip ke <strong>{{ $bill->host_name }}</strong>.
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold d-none" id="roundUpDiffBadge">+Rp 0</span>
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Total Harus Dibayar:</h6>
                        <small class="text-muted">Harga Item + Proporsi Ongkir</small>
                    </div>
                    <span class="fw-bold text-primary fs-3" id="summaryMyTotalPayable">Rp 0</span>
                </div>

                <div class="row g-2">
                    <div class="col-md-7">
                        @if(!empty($bill->qris_static_payload))
                            <button type="button" class="btn btn-primary w-100 py-3 fw-bold fs-6 shadow-sm" id="btnProcessQris">
                                <i class="fa-solid fa-qrcode me-2"></i> Bayar via Dynamic QRIS
                            </button>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <button type="button" class="btn btn-outline-success w-100 py-3 fw-bold fs-6" id="btnTriggerClaimModal">
                            <i class="fa-solid fa-circle-check me-2"></i> Saya Sudah Bayar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- List Pembayar yang Sudah Bayar -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-users text-success"></i>
                    <span>Riwayat Pembayaran ({{ $bill->claims->count() }})</span>
                </h5>
                <small class="text-muted d-block mt-1">Daftar anggota yang sudah konfirmasi bayar</small>
            </div>
            <div class="card-body p-4">
                @if($bill->claims->count() > 0)
                    <div class="vstack gap-2">
                        @foreach($bill->claims->sortByDesc('created_at') as $claim)
                            <div class="p-3 rounded bg-light border d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark">{{ $claim->payer_name }}</span>
                                        <span class="badge bg-success">
                                            <i class="fa-solid fa-circle-check me-1"></i> Lunas
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Item: 
                                        @foreach($claim->claimItems as $cItem)
                                            <span class="badge bg-white text-dark border me-1">{{ $cItem->item->name ?? 'Item' }} ({{ $cItem->qty }}x)</span>
                                        @endforeach
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">Rp {{ number_format($claim->amount, 0, ',', '.') }}</span>
                                    <small class="text-muted d-block fs-8">{{ $claim->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fa-solid fa-receipt fs-4 mb-2 d-block text-secondary"></i>
                        Belum ada anggota yang mengonfirmasi pembayaran.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- DYNAMIC QRIS MODAL -->
<div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark fs-6" id="qrisModalLabel">
                    <i class="fa-solid fa-qrcode text-primary me-2"></i> Pindai QRIS Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <span class="badge bg-primary mb-2 px-3 py-2">
                    Merchant: <strong id="modalMerchantName">-</strong>
                </span>

                <h3 class="fw-bold text-primary display-6 my-2" id="modalNominalDisplay">Rp 0</h3>
                <p class="text-muted small mb-2" id="modalNominalSubtext">Nominal pembayaran sudah terkunci otomatis di QR Code ini.</p>
                <div id="modalRoundUpNote" class="d-none mb-3">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                        <i class="fa-solid fa-heart me-1 text-danger"></i> Termasuk pembulatan ke atas sebagai tanda terima kasih
                    </span>
                </div>

                <!-- Canvas / QR Code Render Area -->
                <div class="p-3 bg-white rounded border d-inline-block shadow-sm mb-3" id="modalQrCodeContainer"></div>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    <button class="btn btn-sm btn-primary rounded-pill px-3" id="btnDownloadQr">
                        <i class="fa-solid fa-download me-1"></i> Simpan Gambar QRIS
                    </button>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnCopyQrisString">
                        <i class="fa-solid fa-copy me-1"></i> Salin String QRIS
                    </button>
                </div>

                <hr class="my-3">

                <!-- Shortcut to Claim inside Modal -->
                <button type="button" class="btn btn-sm btn-success rounded-pill px-4" id="btnOpenClaimFromModal">
                    <i class="fa-solid fa-circle-check me-1"></i> Konfirmasi Saya Sudah Bayar
                </button>
            </div>
            <div class="modal-footer justify-content-center">
                <small class="text-muted">Buka aplikasi E-Wallet (BCA/GoPay/OVO/ShopeePay) lalu scan/upload gambar QRIS di atas.</small>
            </div>
        </div>
    </div>
</div>

<!-- CLAIM PAYMENT CONFIRMATION MODAL -->
<div class="modal fade" id="claimModal" tabindex="-1" aria-labelledby="claimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark fs-6" id="claimModalLabel">
                    <i class="fa-solid fa-circle-check text-success me-2"></i> Konfirmasi Sudah Bayar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Masukkan nama kamu untuk menandai bahwa item pesanan yang kamu pilih sudah dibayar.</p>

                <div class="mb-3">
                    <label class="form-label text-dark small fw-semibold">Nama Kamu (Pembayar)</label>
                    <input type="text" id="claimPayerNameInput" class="form-control" placeholder="Contoh: Fikri" required>
                </div>

                <div class="p-3 rounded bg-light border mb-3">
                    <h6 class="fw-bold text-dark mb-2 small">Rincian Yang Diklaim:</h6>
                    <div id="claimItemsSummaryList" class="small text-muted mb-2"></div>
                    <div id="claimRoundUpRow" class="d-none justify-content-between align-items-center text-primary small mb-2">
                        <span><i class="fa-solid fa-arrow-trend-up me-1"></i> Pembulatan ke Atas (Tip):</span>
                        <span class="fw-semibold" id="claimRoundUpAmount">+Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fw-bold text-dark small">Total Tagihan:</span>
                        <span class="fw-bold text-success fs-5" id="claimTotalDisplay">Rp 0</span>
                    </div>
                </div>

                <button type="button" class="btn btn-success w-100 py-3 fw-bold fs-6" id="btnSubmitClaim">
                    <i class="fa-solid fa-check-double me-1"></i> Simpan Konfirmasi Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<!-- RECEIPT VIEW MODAL -->
@if($bill->receipt_image_path)
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark fs-6" id="receiptModalLabel">
                    <i class="fa-solid fa-receipt text-info me-2"></i> Foto Struk / Nota Pesanan Asli
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div class="p-2 rounded bg-light border d-inline-block w-100" style="max-height: 70vh; overflow-y: auto;">
                    <img src="{{ route('bills.receipt', ['slug' => $bill->slug], false) }}" alt="Struk Asli" class="img-fluid rounded border shadow-sm" onerror="this.onerror=null; this.src='{{ asset('storage/' . $bill->receipt_image_path) }}';">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted"><i class="fa-solid fa-eye me-1"></i> Transparansi total: foto struk diunggah langsung oleh Host ({{ $bill->host_name }}).</small>
                <a href="{{ route('bills.receipt', ['slug' => $bill->slug], false) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                    <i class="fa-solid fa-up-right-from-square me-1"></i> Buka Gambar Penuh
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billSlug = "{{ $bill->slug }}";
    const totalBillSubtotal = {{ $bill->subtotal }};
    const netExtraFees = {{ $bill->net_extra_fees }};

    const participantItemsList = document.getElementById('participantItemsList');
    const summaryMyItemsSubtotal = document.getElementById('summaryMyItemsSubtotal');
    const summaryMyFeeShare = document.getElementById('summaryMyFeeShare');
    const summaryMyTotalPayable = document.getElementById('summaryMyTotalPayable');
    const btnProcessQris = document.getElementById('btnProcessQris');

    const toggleRoundUp = document.getElementById('toggleRoundUp');
    const roundUpDiffBadge = document.getElementById('roundUpDiffBadge');
    const roundUpHelperText = document.getElementById('roundUpHelperText');

    const qrisModal = new bootstrap.Modal(document.getElementById('qrisModal'));
    const claimModal = new bootstrap.Modal(document.getElementById('claimModal'));
    
    const modalMerchantName = document.getElementById('modalMerchantName');
    const modalNominalDisplay = document.getElementById('modalNominalDisplay');
    const modalRoundUpNote = document.getElementById('modalRoundUpNote');
    const modalQrCodeContainer = document.getElementById('modalQrCodeContainer');
    const btnDownloadQr = document.getElementById('btnDownloadQr');
    const btnCopyQrisString = document.getElementById('btnCopyQrisString');

    const btnTriggerClaimModal = document.getElementById('btnTriggerClaimModal');
    const btnOpenClaimFromModal = document.getElementById('btnOpenClaimFromModal');
    const claimPayerNameInput = document.getElementById('claimPayerNameInput');
    const claimItemsSummaryList = document.getElementById('claimItemsSummaryList');
    const claimRoundUpRow = document.getElementById('claimRoundUpRow');
    const claimRoundUpAmount = document.getElementById('claimRoundUpAmount');
    const claimTotalDisplay = document.getElementById('claimTotalDisplay');
    const btnSubmitClaim = document.getElementById('btnSubmitClaim');

    let currentQrisPayload = '';
    let currentTotalPayable = 0;

    // Copy Share Link
    const btnCopyShareLink = document.getElementById('btnCopyShareLink');
    if (btnCopyShareLink) {
        btnCopyShareLink.addEventListener('click', function() {
            let shareUrl = "{{ $shareUrl }}";
            if (!shareUrl || shareUrl.includes(':///')) {
                shareUrl = window.location.href;
            }
            navigator.clipboard.writeText(shareUrl);
            alert('Link patungan berhasil disalin ke clipboard!');
        });
    }

    // Copy Bank Account
    document.querySelectorAll('.btn-copy-bank').forEach(btn => {
        btn.addEventListener('click', function() {
            const bankAcc = this.dataset.acc;
            const bankName = this.dataset.bank;
            navigator.clipboard.writeText(bankAcc);
            alert(`Nomor rekening/HP ${bankName} (${bankAcc}) berhasil disalin!`);
        });
    });

    // Claim item quantity controls
    participantItemsList.addEventListener('click', function(e) {
        const btnMinus = e.target.closest('.btn-claim-minus');
        const btnPlus = e.target.closest('.btn-claim-plus');
        if (!btnMinus && !btnPlus) return;

        const card = e.target.closest('.item-claim-card');
        const qtyInput = card.querySelector('.claim-qty');
        const maxQty = parseInt(card.dataset.itemMax) || 0;
        let currentQty = parseInt(qtyInput.value) || 0;

        if (btnMinus && currentQty > 0) {
            currentQty--;
        } else if (btnPlus && currentQty < maxQty) {
            currentQty++;
        }

        qtyInput.value = currentQty;
        updateCardTotal(card, currentQty);
        recalculateParticipantSummary();
    });

    function updateCardTotal(card, qty) {
        const price = parseFloat(card.dataset.itemPrice) || 0;
        const total = qty * price;
        card.querySelector('.item-claim-total').innerText = formatRupiah(total);
    }

    function getSelectedItemsData() {
        const items = {};
        const itemsList = [];
        let subtotal = 0;

        const cards = participantItemsList.querySelectorAll('.item-claim-card');
        cards.forEach(card => {
            const itemId = card.dataset.itemId;
            const itemName = card.dataset.itemName;
            const qty = parseInt(card.querySelector('.claim-qty')?.value) || 0;
            const price = parseFloat(card.dataset.itemPrice) || 0;

            if (qty > 0) {
                items[itemId] = qty;
                itemsList.push({ id: itemId, name: itemName, qty: qty, price: price });
                subtotal += (qty * price);
            }
        });

        let feeShare = 0;
        if (totalBillSubtotal > 0 && subtotal > 0) {
            const proportion = subtotal / totalBillSubtotal;
            feeShare = proportion * netExtraFees;
        }

        const exactPayable = Math.max(0, Math.round(subtotal + feeShare));
        const isRoundUp = toggleRoundUp ? toggleRoundUp.checked : false;

        let totalPayable = exactPayable;
        let roundUpExtra = 0;

        if (isRoundUp && exactPayable > 0) {
            const rounded = Math.ceil(exactPayable / 1000) * 1000;
            roundUpExtra = Math.max(0, rounded - exactPayable);
            totalPayable = rounded;
        }

        return { items, itemsList, subtotal, feeShare, exactPayable, roundUpExtra, isRoundUp, totalPayable };
    }

    function recalculateParticipantSummary() {
        const data = getSelectedItemsData();
        currentTotalPayable = data.totalPayable;

        summaryMyItemsSubtotal.innerText = formatRupiah(data.subtotal);
        summaryMyFeeShare.innerText = formatRupiah(data.feeShare);
        summaryMyTotalPayable.innerText = formatRupiah(data.totalPayable);

        if (toggleRoundUp && toggleRoundUp.checked && data.subtotal > 0) {
            roundUpDiffBadge.classList.remove('d-none');
            roundUpDiffBadge.innerText = '+Rp ' + data.roundUpExtra.toLocaleString('id-ID');
            if (data.roundUpExtra > 0) {
                roundUpHelperText.innerHTML = `Dibulatkan dari <strong>${formatRupiah(data.exactPayable)}</strong> (+${formatRupiah(data.roundUpExtra)} tip/terima kasih)`;
            } else {
                roundUpHelperText.innerHTML = `Nominal sudah pas ribuan (<strong>${formatRupiah(data.exactPayable)}</strong>)`;
            }
        } else if (toggleRoundUp) {
            roundUpDiffBadge.classList.add('d-none');
            roundUpHelperText.innerHTML = `Lebihkan sedikit nominal transfer sebagai tanda terima kasih / tip ke <strong>${escapeHtml("{{ $bill->host_name }}")}</strong>.`;
        }
    }

    if (toggleRoundUp) {
        toggleRoundUp.addEventListener('change', recalculateParticipantSummary);
    }

    // Process Dynamic QRIS
    if (btnProcessQris) {
        btnProcessQris.addEventListener('click', async function() {
            const selection = getSelectedItemsData();
            if (Object.keys(selection.items).length === 0) {
                alert('Silakan pilih minimal 1 item pesanan kamu terlebih dahulu!');
                return;
            }

            btnProcessQris.disabled = true;
            btnProcessQris.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menggenerate QRIS Dinamis...';

            try {
                const response = await fetch(`/b/${billSlug}/qris`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        items: selection.items,
                        round_up: selection.isRoundUp
                    })
                });

                const data = await response.json();
                btnProcessQris.disabled = false;
                btnProcessQris.innerHTML = '<i class="fa-solid fa-qrcode me-2"></i> Bayar via Dynamic QRIS';

                if (data.success && data.dynamic_qris_payload) {
                    currentQrisPayload = data.dynamic_qris_payload;
                    modalMerchantName.innerText = data.merchant_name || 'Merchant';
                    modalNominalDisplay.innerText = formatRupiah(data.total_payable);

                    if (data.round_up_extra > 0) {
                        modalRoundUpNote.classList.remove('d-none');
                    } else {
                        modalRoundUpNote.classList.add('d-none');
                    }

                    // Render QR Code
                    modalQrCodeContainer.innerHTML = '';
                    new QRCode(modalQrCodeContainer, {
                        text: data.dynamic_qris_payload,
                        width: 240,
                        height: 240,
                        correctLevel: QRCode.CorrectLevel.M
                    });

                    qrisModal.show();
                } else {
                    alert('Gagal meng-generate QRIS dinamis. Pastikan QRIS statis valid.');
                }
            } catch (err) {
                btnProcessQris.disabled = false;
                btnProcessQris.innerHTML = '<i class="fa-solid fa-qrcode me-2"></i> Bayar via Dynamic QRIS';
                alert('Terjadi kesalahan saat memproses QRIS.');
            }
        });
    }

    // Open Claim Modal Handlers
    btnTriggerClaimModal.addEventListener('click', openClaimModal);
    if (btnOpenClaimFromModal) {
        btnOpenClaimFromModal.addEventListener('click', function() {
            qrisModal.hide();
            openClaimModal();
        });
    }

    function openClaimModal() {
        const selection = getSelectedItemsData();
        if (Object.keys(selection.items).length === 0) {
            alert('Silakan pilih minimal 1 item pesanan kamu terlebih dahulu!');
            return;
        }

        let html = '<ul class="mb-0 ps-3 text-muted">';
        selection.itemsList.forEach(item => {
            html += `<li>${escapeHtml(item.name)} (${item.qty}x)</li>`;
        });
        html += '</ul>';

        claimItemsSummaryList.innerHTML = html;

        if (selection.isRoundUp && selection.roundUpExtra > 0) {
            claimRoundUpRow.classList.remove('d-none');
            claimRoundUpRow.classList.add('d-flex');
            claimRoundUpAmount.innerText = '+Rp ' + selection.roundUpExtra.toLocaleString('id-ID');
        } else {
            claimRoundUpRow.classList.add('d-none');
            claimRoundUpRow.classList.remove('d-flex');
        }

        claimTotalDisplay.innerText = formatRupiah(selection.totalPayable);

        claimModal.show();
    }

    // Submit Claim
    btnSubmitClaim.addEventListener('click', async function() {
        const payerName = claimPayerNameInput.value.trim();
        if (!payerName) {
            alert('Harap masukkan Nama Kamu!');
            claimPayerNameInput.focus();
            return;
        }

        const selection = getSelectedItemsData();
        if (Object.keys(selection.items).length === 0) {
            alert('Tidak ada item yang dipilih!');
            return;
        }

        btnSubmitClaim.disabled = true;
        btnSubmitClaim.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';

        try {
            const response = await fetch(`/b/${billSlug}/claim`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    payer_name: payerName,
                    items: selection.items,
                    round_up: selection.isRoundUp
                })
            });

            const data = await response.json();
            btnSubmitClaim.disabled = false;
            btnSubmitClaim.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> Simpan Konfirmasi Pembayaran';

            if (data.success) {
                alert(data.message);
                claimModal.hide();
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan klaim pembayaran.');
            }
        } catch (err) {
            btnSubmitClaim.disabled = false;
            btnSubmitClaim.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> Simpan Konfirmasi Pembayaran';
            alert('Terjadi kesalahan jaringan saat menyimpan klaim.');
        }
    });

    // Download QR Image
    btnDownloadQr.addEventListener('click', function() {
        const qrCanvas = modalQrCodeContainer.querySelector('canvas');
        const qrImg = modalQrCodeContainer.querySelector('img');

        let dataUrl = '';
        if (qrCanvas) {
            dataUrl = qrCanvas.toDataURL('image/png');
        } else if (qrImg) {
            dataUrl = qrImg.src;
        }

        if (dataUrl) {
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = `QRIS_PayMe_${billSlug}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert('Gambar QR Code tidak tersedia.');
        }
    });

    // Copy QRIS String
    btnCopyQrisString.addEventListener('click', function() {
        if (currentQrisPayload) {
            navigator.clipboard.writeText(currentQrisPayload);
            alert('String QRIS berhasil disalin!');
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
});
</script>
@endsection
