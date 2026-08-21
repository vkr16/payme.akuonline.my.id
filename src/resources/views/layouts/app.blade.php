<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PayMe - QRIS Split Bill Generator')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/qrlogo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/qrlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/qrlogo.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Font Awesome v7 -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.css') }}">

    <!-- Client-side QR JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.3.1/dist/jsQR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .upload-dropzone {
            border: 2px dashed #ced4da;
            background-color: #ffffff;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.15s ease-in-out, background-color 0.15s ease-in-out;
        }

        .upload-dropzone:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-light text-dark">
    <!-- Navbar (Light Theme) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('bills.create') }}">
                <img src="{{ asset('images/qrlogo.png') }}" alt="PayMe" width="34" height="34" class="d-inline-block rounded">
                <div class="d-flex flex-column lh-1">
                    <span class="fw-bold text-primary fs-4 mb-0">PayMe</span>
                    <span class="text-muted fw-normal" style="font-size: 0.68rem; margin-top: 2px;">by AkuOnline</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot me-1"></i> Buy Me Coffee
                </button>
                <a href="{{ route('bills.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-plus me-1"></i> Buat Patungan
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted border-top bg-white mt-auto">
        <div class="container">
            <p class="mb-1 small">&copy; {{ date('Y') }} PayMe &bull; QRIS Static to Dynamic Split Bill</p>
            <p class="mb-0 small text-secondary">
                Dibuat oleh <strong>Fikri M</strong> dari <strong>AkuOnline</strong> menggunakan <span class="text-primary fw-semibold"><i class="fa-solid fa-rocket me-1"></i>Antigravity</span>
            </p>
        </div>
    </footer>

    <!-- BUY ME A COFFEE MODAL -->
    <div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10 border-warning border-opacity-25">
                    <h5 class="modal-title fw-bold text-dark fs-6" id="coffeeModalLabel">
                        <i class="fa-solid fa-mug-hot text-warning me-2 fs-5"></i> Traktir Kopi (Buy Me a Coffee)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">Suka dengan aplikasi <strong>PayMe</strong>? Dukung pengembang agar aplikasi ini tetap gratis & aktif dikembangkan!</p>

                    <div class="p-3 bg-light rounded border d-inline-block shadow-sm mb-3">
                        <img src="{{ asset('images/qris-coffee.png') }}" alt="QRIS Support" class="img-fluid rounded" style="max-width: 260px;" onerror="this.onerror=null; this.src='https://via.placeholder.com/260x260?text=Scan+QRIS+Kopi';">
                    </div>

                    <h6 class="fw-bold text-dark mb-1">Fikri M - AkuOnline</h6>
                    <small class="text-muted d-block">Terima kasih banyak atas apresiasi dan dukungannya! ☕❤️</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @yield('scripts')
</body>
</html>
