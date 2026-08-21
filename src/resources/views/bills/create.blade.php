@extends('layouts.app')

@section('title', 'Buat Patungan Baru - PayMe')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="fw-bold fs-3 text-dark mb-1">
                <i class="fa-solid fa-receipt text-primary me-2"></i> Buat Patungan Baru
            </h1>
            <p class="text-muted mb-0">Upload QRIS statis & nota pesanan kamu untuk membagikan biaya patungan secara otomatis dan presisi.</p>
        </div>

        <form action="{{ route('bills.store') }}" method="POST" enctype="multipart/form-data" id="billForm">
            @csrf

            <!-- STEP 1: INFORMASI PATUNGAN -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary fs-6">
                        <i class="fa-solid fa-user me-2"></i> Informasi Patungan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Nama Penggalang (Host / Ditalangin Oleh)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="host_name" class="form-control" placeholder="Contoh: Leo" value="{{ old('host_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Judul Kegiatan / Pesanan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-utensils"></i></span>
                                <input type="text" name="title" class="form-control" placeholder="Contoh: Makan Siang ShopeeFood IT Team" value="{{ old('title') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: UPLOAD QRIS STATIS -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-primary fs-6">
                        <i class="fa-solid fa-qrcode me-2"></i> QRIS Statis Penerima Transfer
                    </h5>
                    <span class="badge bg-primary">Auto Decode</span>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <label class="upload-dropzone w-100 mb-0" for="qrisFileInput">
                                <i class="fa-solid fa-cloud-arrow-up text-primary fs-2 mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block mb-1">Pilih Gambar QRIS Statis Kamu</span>
                                <span class="text-muted small">Format PNG, JPG, JPEG (Maks. 10MB)</span>
                                <input type="file" name="qris_image" id="qrisFileInput" class="d-none" accept="image/*">
                            </label>
                            <!-- Hidden input for raw QRIS EMVCo string payload -->
                            <input type="hidden" name="qris_static_payload" id="qrisStaticPayload" value="{{ old('qris_static_payload') }}">
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 rounded bg-light border text-center" id="qrisPreviewContainer">
                                <div id="qrisEmptyState" class="py-3 text-muted small">
                                    <i class="fa-solid fa-image me-1"></i> Belum ada gambar QRIS dipilih
                                </div>
                                <canvas id="qrisCanvas" class="d-none max-w-100 rounded border mb-2" style="max-height: 160px;"></canvas>
                                <div id="qrisMerchantInfo" class="d-none">
                                    <span class="badge bg-success mb-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> QRIS Valid
                                    </span>
                                    <h6 id="merchantNameText" class="fw-bold text-dark mb-0 mt-1"></h6>
                                    <small id="merchantCityText" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: STRUK PESANAN & AI SCANNER -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0 fw-bold text-primary fs-6">
                        <i class="fa-solid fa-receipt me-2"></i> Rincian Struk Pesanan
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="receiptFileInput" class="btn btn-sm btn-outline-primary mb-0 cursor-pointer">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Pindai Struk via AI
                            <input type="file" name="receipt_image" id="receiptFileInput" class="d-none" accept="image/*">
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddManualItem">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Item Manual
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">

                    <!-- Loading Indicator for AI -->
                    <div id="aiLoading" class="alert alert-info d-none text-center py-3 mb-4">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span class="fw-semibold">Menganalisis gambar struk via AI Vision... Harap tunggu sebentar.</span>
                    </div>

                    <!-- Alert message -->
                    <div id="aiAlert" class="alert d-none rounded-3 mb-3"></div>

                    <!-- Mobile-Friendly Items Card List (No Table!) -->
                    <div id="itemsContainer" class="vstack gap-2">
                        <!-- Dynamic item cards -->
                    </div>

                    <div id="noItemsState" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-list-check fs-3 mb-2 d-block text-secondary"></i>
                        Belum ada item. Silakan pindai struk atau klik <strong>+ Tambah Item Manual</strong>.
                    </div>

                    <!-- Subtotal & Extra Fees -->
                    <div class="border-top mt-4 pt-4">
                        <div class="row g-3 justify-content-end">
                            <div class="col-md-7 col-lg-6">
                                <div class="p-3 rounded bg-light border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small">Subtotal Item:</span>
                                        <span class="fw-bold text-dark" id="displayItemsSubtotal">Rp 0</span>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label text-muted small mb-1">Ongkos Kirim</label>
                                            <input type="text" inputmode="numeric" name="delivery_fee" id="inputDeliveryFee" class="form-control form-control-sm currency-input" placeholder="0" value="{{ old('delivery_fee', 0) }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small mb-1">Biaya Layanan</label>
                                            <input type="text" inputmode="numeric" name="service_fee" id="inputServiceFee" class="form-control form-control-sm currency-input" placeholder="0" value="{{ old('service_fee', 0) }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1">Total Diskon / Voucher</label>
                                        <input type="text" inputmode="numeric" name="discount" id="inputDiscount" class="form-control form-control-sm text-success fw-bold currency-input" placeholder="0" value="{{ old('discount', 0) }}">
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

            <!-- STEP 4: TRANSFER BANK ALTERNATIF -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-primary fs-6">
                        <i class="fa-solid fa-building-columns me-2"></i> Rekening Bank Alternatif (Opsional)
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="toggleBankOption">
                        <label class="form-check-label text-muted small" for="toggleBankOption">Aktifkan Opsi Bank</label>
                    </div>
                </div>
                <div class="card-body p-4 d-none" id="bankFieldsContainer">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-dark small fw-semibold">Nama Bank / E-Wallet</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="Contoh: BCA / Mandiri / GoPay" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark small fw-semibold">Nomor Rekening / HP</label>
                            <input type="text" name="bank_account_number" class="form-control" placeholder="Contoh: 1234567890" value="{{ old('bank_account_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-dark small fw-semibold">Atas Nama (A.N)</label>
                            <input type="text" name="bank_account_holder" class="form-control" placeholder="Contoh: Leo Suwandi" value="{{ old('bank_account_holder') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBMIT ACTION -->
            <div class="text-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow-sm">
                    <i class="fa-solid fa-paper-plane me-2"></i> Simpan & Buat Link Patungan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;

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

    toggleBankOption.addEventListener('change', function() {
        if (this.checked) {
            bankFieldsContainer.classList.remove('d-none');
        } else {
            bankFieldsContainer.classList.add('d-none');
        }
    });

    qrisFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
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
                    qrisStaticPayload.value = code.data;
                    qrisMerchantInfo.classList.remove('d-none');

                    const merchant = parseMerchantTags(code.data);
                    merchantNameText.innerText = merchant.name || 'Merchant QRIS';
                    merchantCityText.innerText = merchant.location ? 'Lokasi: ' + merchant.location : '';
                } else {
                    qrisMerchantInfo.classList.remove('d-none');
                    merchantNameText.innerText = 'Gambar QRIS Tidak Terbaca Auto';
                    merchantCityText.innerText = 'Format string payload dapat diproses di server.';
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });

    function parseMerchantTags(qrisStr) {
        const nameMatch = qrisStr.match(/59(\d{2})([^\d]{2,})/);
        const locMatch = qrisStr.match(/60(\d{2})([^\d]{2,})/);

        const name = nameMatch ? nameMatch[2].substring(0, parseInt(nameMatch[1], 10)) : '';
        const location = locMatch ? locMatch[2].substring(0, parseInt(locMatch[1], 10)) : '';

        return { name, location };
    }

    receiptFileInput.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        aiLoading.classList.remove('d-none');
        aiAlert.classList.add('d-none');

        const formData = new FormData();
        formData.append('receipt_image', file);

        try {
            const response = await fetch("{{ route('bills.parse-receipt', [], false) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();
            aiLoading.classList.add('d-none');

            if (data.success && data.items && data.items.length > 0) {
                aiAlert.className = 'alert alert-success rounded-3 mb-3';
                aiAlert.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles me-2"></i> AI berhasil mengekstrak ${data.items.length} item dari struk! Silakan periksa kembali nilainya.`;
                aiAlert.classList.remove('d-none');

                itemsContainer.innerHTML = '';
                itemIndex = 0;

                data.items.forEach(item => {
                    addItemRow(item.name, item.qty, item.price);
                });

                if (data.delivery_fee) inputDeliveryFee.value = formatThousand(data.delivery_fee);
                if (data.service_fee) inputServiceFee.value = formatThousand(data.service_fee);
                if (data.discount) inputDiscount.value = formatThousand(data.discount);

                recalculateTotals();

            } else {
                aiAlert.className = 'alert alert-warning rounded-3 mb-3';
                aiAlert.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> ${data.error || 'AI tidak dapat membaca detail item struk secara otomatis. Silakan masukkan item secara manual.'}`;
                aiAlert.classList.remove('d-none');
            }
        } catch (err) {
            aiLoading.classList.add('d-none');
            aiAlert.className = 'alert alert-danger rounded-3 mb-3';
            aiAlert.innerHTML = `<i class="fa-solid fa-circle-exclamation me-2"></i> Gagal menghubungi service AI Vision.`;
            aiAlert.classList.remove('d-none');
        }
    });

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
                        <label class="form-label text-muted small mb-1 fw-bold">Nama Item</label>
                        <input type="text" name="items[${itemIndex}][name]" class="form-control form-control-sm item-name" placeholder="Nama item" value="${escapeHtml(name)}" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted small mb-1 fw-bold">Jumlah (Qty)</label>
                        <div class="input-group input-group-sm">
                            <button type="button" class="btn btn-outline-secondary btn-minus"><i class="fa-solid fa-minus"></i></button>
                            <input type="number" name="items[${itemIndex}][qty]" class="form-control text-center item-qty" value="${qty}" min="1" required>
                            <button type="button" class="btn btn-outline-secondary btn-plus"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label text-muted small mb-1 fw-bold">Harga Satuan (Rp)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" inputmode="numeric" name="items[${itemIndex}][price]" class="form-control form-control-sm item-price currency-input" placeholder="0" value="${formatThousand(price)}" required>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-row" title="Hapus Item">
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

    // On Form Submit: strip formatting (dots) so backend receives clean integers
    billForm.addEventListener('submit', function() {
        document.querySelectorAll('.currency-input').forEach(input => {
            input.value = parseRawNumber(input.value);
        });
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
