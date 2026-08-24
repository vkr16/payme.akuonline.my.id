@extends('layouts.app')

@section('title', 'PayMe - Solusi Hitung Patungan & QRIS Dinamis Otomatis')

@section('content')
<div class="py-4 py-md-5">
    <!-- Hero Header -->
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 mb-3 fw-semibold small">
                <i class="fa-solid fa-bolt text-primary"></i>
                <span>Solusi Patungan Pintar No. 1</span>
            </div>
            
            <h1 class="fw-extrabold display-5 text-dark mb-3 tracking-tight" style="letter-spacing: -0.03em;">
                Hitung Patungan & Pembayaran QRIS Otomatis
            </h1>
            
            <p class="lead text-muted mb-4 px-md-4">
                Konversi gambar QRIS statis menjadi QRIS dinamis ber-nominal presisi dan ekstrak rincian struk belanja secara otomatis menggunakan kecerdasan AI Vision.
            </p>
            
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                <a href="{{ route('bills.create') }}" class="btn btn-gradient-primary btn-lg btn-pill px-4 py-2.5 d-inline-flex align-items-center gap-2 shadow-sm fs-6">
                    <i class="fa-solid fa-plus"></i>
                    <span>Buat Tagihan Patungan</span>
                </a>
                <button type="button" class="btn btn-outline-secondary btn-lg btn-pill px-4 py-2.5 d-inline-flex align-items-center gap-2 fs-6" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot text-warning"></i>
                    <span>Dukung Pengembang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Feature Cards Grid -->
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm glass-card">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3" style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-qrcode fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">QRIS Statis ke Dinamis</h5>
                <p class="text-muted small mb-0">
                    Otomatis mengubah kode QRIS toko kamu menjadi QRIS dinamis sesuai nominal porsi pesanan tiap teman.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm glass-card">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-teal bg-opacity-10 text-teal mb-3" style="width: 52px; height: 52px; background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                    <i class="fa-solid fa-wand-magic-sparkles fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">AI Vision Receipt Scanner</h5>
                <p class="text-muted small mb-0">
                    Ekstraksi otomatis item, kuantitas, nominal, diskon, hingga biaya penanganan dari foto struk belanjaan.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 shadow-sm glass-card">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-indigo bg-opacity-10 text-indigo mb-3" style="width: 52px; height: 52px; background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                    <i class="fa-solid fa-calculator fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Perhitungan Adil & Akurat</h5>
                <p class="text-muted small mb-0">
                    Perhitungan proporsional untuk biaya pengiriman, layanan, dan diskon promo agar setiap teman membayar secara adil.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
