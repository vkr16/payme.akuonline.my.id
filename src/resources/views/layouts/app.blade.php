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

    <!-- Self-Hosted Client-side QR JS Libraries -->
    <script src="{{ asset('vendor/jsqr/jsQR.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>

    <style>
        :root {
            --bs-body-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --bs-primary: #2563eb;
            --bs-primary-rgb: 37, 99, 235;
        }

        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Modern card styling */
        .card {
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Interactive Upload Dropzone */
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            background-color: #f8fafc;
            border-radius: 0.75rem;
            padding: 1.75rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: block;
        }

        .upload-dropzone:hover,
        .upload-dropzone:focus-within,
        .upload-dropzone.dragover {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        /* Form input enhancements */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.55rem 0.85rem;
            font-size: 0.95rem;
            color: #1e293b;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        /* Minimum touch targets for mobile accessibility */
        .btn-touch-target {
            min-height: 44px;
            min-width: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Stepper buttons */
        .stepper-btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 0.375rem;
        }

        @media (pointer: coarse) {
            .stepper-btn {
                width: 44px;
                height: 44px;
            }
        }

        /* Badge and subtle pill styles */
        .badge {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    @yield('styles')
</head>
<body class="bg-light text-dark">
    <!-- Navbar (Light Theme) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('bills.create') }}">
                <img src="{{ asset('images/qrlogo.png') }}" alt="PayMe" width="34" height="34" class="d-inline-block rounded">
                <div class="d-flex flex-column lh-1">
                    <span class="fw-bold text-primary fs-4 mb-0">PayMe</span>
                    <span class="text-muted fw-normal" style="font-size: 0.68rem; margin-top: 2px;">by AkuOnline</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning btn-sm fw-semibold rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot me-1"></i>
                    <span>Traktir Kopi</span>
                </button>
                <a href="{{ route('bills.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-plus me-1"></i>
                    <span>Buat Patungan</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-check fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted border-top bg-white mt-auto">
        <div class="container">
            <p class="mb-1 small">&copy; {{ date('Y') }} PayMe &bull; QRIS Static to Dynamic Split Bill</p>
            <p class="mb-0 small text-secondary">
                Dikembangkan oleh <strong>Fikri M</strong> dari <strong>AkuOnline</strong> menggunakan <span class="text-primary fw-semibold"><i class="fa-solid fa-rocket me-1"></i>Antigravity</span>
            </p>
        </div>
    </footer>

    <!-- BUY ME A COFFEE MODAL -->
    <div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning bg-opacity-10 border-warning border-opacity-25">
                    <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center" id="coffeeModalLabel">
                        <i class="fa-solid fa-mug-hot text-warning me-2 fs-5"></i> Traktir Kopi (Buy Me a Coffee)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">Suka dengan kemudahan aplikasi <strong>PayMe</strong>? Dukung pengembang agar layanan ini tetap gratis, stabil, dan terus dikembangkan secara berkelanjutan.</p>

                    <div class="p-3 bg-light rounded-3 border d-inline-block shadow-sm mb-3">
                        <img src="{{ asset('images/qris-coffee.png') }}" alt="QRIS Dukungan Kopi" class="img-fluid rounded" style="max-width: 240px;">
                    </div>

                    <h6 class="fw-bold text-dark mb-1">Fikri M - AkuOnline</h6>
                    <small class="text-muted d-block">
                        <i class="fa-solid fa-heart text-danger me-1"></i> Terima kasih atas dukungan dan apresiasi Anda!
                    </small>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
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
