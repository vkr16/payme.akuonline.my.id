@extends('layouts.app')

@section('title', 'PayMe - QRIS Split Bill Generator')

@section('content')
<div class="row justify-content-center text-center py-5">
    <div class="col-md-8 col-lg-6">
        <img src="{{ asset('images/qrlogo.png') }}" alt="PayMe" width="80" height="80" class="rounded-4 mb-3 shadow-sm">
        <h1 class="fw-bold fs-2 text-dark mb-2">PayMe by AkuOnline</h1>
        <p class="text-muted mb-4">Solusi patungan pintar dengan konversi QRIS statis ke dinamis dan pemindaian struk otomatis berbasis AI Vision.</p>
        <a href="{{ route('bills.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Buat Patungan Sekarang</span>
        </a>
    </div>
</div>
@endsection

