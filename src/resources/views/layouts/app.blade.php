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

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    <!-- Font Awesome v7 -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.css') }}">

    <!-- Self-Hosted Client-side QR JS Libraries -->
    <script src="{{ asset('vendor/jsqr/jsQR.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --bs-primary: #0284c7;
            --bs-primary-rgb: 2, 132, 199;
            --payme-primary: #0284c7;
            --payme-teal: #0d9488;
            --payme-indigo: #4f46e5;
            --payme-bg: #f8fafc;
        }

        body {
            background-color: var(--payme-bg);
            background-image:
                radial-gradient(at 0% 0%, rgba(2, 132, 199, 0.06) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(13, 148, 136, 0.06) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(79, 70, 229, 0.03) 0px, transparent 70%);
            background-attachment: fixed;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            scrollbar-gutter: stable;
            overscroll-behavior: contain;
        }

        /* Glassmorphism Navigation Bar */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.82) !important;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
        }

        .brand-logo-glow {
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .brand-logo-glow:hover {
            transform: scale(1.05);
        }

        /* Glassmorphism Card Styling */
        .card, .glass-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.6);
            border-bottom: 1px solid rgba(241, 245, 249, 0.9);
            border-top-left-radius: 1.25rem !important;
            border-top-right-radius: 1.25rem !important;
        }

        /* Modern Gradient Buttons */
        .btn-gradient-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #0369a1 0%, #0f766e 100%);
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
            transform: translateY(-1px);
        }

        .btn-pill {
            border-radius: 9999px;
        }

        .btn-rounded {
            border-radius: 0.75rem;
        }

        /* Interactive Upload Dropzone with Glass */
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            background: rgba(248, 250, 252, 0.8);
            border-radius: 1rem;
            padding: 2rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: block;
        }

        .upload-dropzone:hover,
        .upload-dropzone:focus-within,
        .upload-dropzone.dragover {
            border-color: var(--payme-primary);
            background: rgba(240, 249, 255, 0.9);
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1);
            transform: scale(1.005);
        }

        /* Form input enhancements */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.65rem 0.95rem;
            font-size: 0.95rem;
            color: #0f172a;
            background-color: rgba(255, 255, 255, 0.9);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--payme-primary);
            box-shadow: 0 0 0 3.5px rgba(2, 132, 199, 0.15);
            background-color: #ffffff;
        }

        /* Watermark LUNAS Rubber Stamp Overlay */
        .lunas-stamp-overlay {
            position: absolute;
            top: 260px;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-14deg);
            pointer-events: none;
            z-index: 10;
            user-select: none;
            opacity: 0.3;
        }

        .lunas-stamp-badge {
            border: 8px double #053d96;
            color: #070596;
            padding: 1.25rem 3.5rem;
            border-radius: 1.5rem;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.1);
            background: rgba(255, 255, 255, 0.5);
            animation: stampBounce 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .lunas-stamp-inner span {
            font-size: 5rem;
            font-weight: 900;
            letter-spacing: 14px;
            line-height: 1;
            display: block;
            text-transform: uppercase;
        }

        @keyframes stampBounce {
            0% {
                transform: scale(2.5) rotate(-35deg);
                opacity: 0;
            }
            100% {
                transform: scale(1) rotate(-14deg);
                opacity: 0.3;
            }
        }

        /* Touch Targets for Mobile Accessibility */
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
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
        }

        @media (pointer: coarse) {
            .stepper-btn {
                width: 44px;
                height: 44px;
            }
        }

        /* Badge and pill styles */
        .badge {
            font-weight: 600;
            letter-spacing: 0.01em;
            border-radius: 9999px;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Glass Modal */
        .modal-content.glass-modal {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
        }
    </style>

    @yield('styles')
</head>
<body class="text-dark">
    <!-- Navbar (Glassmorphism Header) -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-glass sticky-top py-2.5">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2.5" href="{{ route('bills.create') }}">
                <img src="{{ asset('images/qrlogo.png') }}" alt="PayMe" width="38" height="38" class="d-inline-block rounded-3 brand-logo-glow">
                <div class="d-flex flex-column lh-1 ms-3">
                    <span class="fw-extrabold text-dark fs-4 mb-0 tracking-tight" style="letter-spacing: -0.03em;">PayMe</span>
                    <span class="text-muted fw-medium" style="font-size: 0.68rem; margin-top: 2px;">by AkuOnline</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning btn-sm fw-bold btn-pill px-3 shadow-xs d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot text-dark"></i>
                    <span>Traktir Kopi</span>
                </button>
                <a href="{{ route('bills.create') }}" class="btn btn-gradient-primary btn-sm btn-pill px-3.5 d-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>Buat Patungan</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm rounded-4 bg-success bg-opacity-10 text-success border border-success border-opacity-25" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-check fs-5 me-2 flex-shrink-0"></i>
                    <div class="fw-semibold">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm rounded-4 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation fs-5 me-2 flex-shrink-0"></i>
                    <div class="fw-semibold">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-muted border-top bg-white bg-opacity-50 mt-auto backdrop-blur">
        <div class="container">
            <p class="mb-1 small font-medium">&copy; {{ date('Y') }} PayMe &bull; Solusi Patungan QRIS Statis ke Dinamis</p>
            <p class="mb-0 small text-secondary">
                Dikembangkan oleh <strong>Fikri M</strong> dari <strong>AkuOnline</strong> menggunakan <span class="text-primary fw-semibold"><i class="fa-solid fa-rocket me-1"></i>Antigravity</span>
            </p>
        </div>
    </footer>

    <!-- BUY ME A COFFEE MODAL -->
    <div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-modal border-0">
                <div class="modal-header border-bottom border-light py-3">
                    <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center" id="coffeeModalLabel">
                        <i class="fa-solid fa-mug-hot text-warning me-2 fs-5"></i> Traktir Kopi (Buy Me a Coffee)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <p class="text-muted small mb-3">Suka dengan kemudahan aplikasi <strong>PayMe</strong>? Dukung pengembang agar layanan ini tetap gratis, stabil, dan terus dikembangkan secara berkelanjutan.</p>

                    <div class="p-3 bg-white rounded-4 border d-inline-block shadow-sm mb-3">
                        <img src="{{ asset('images/qris-coffee.png') }}" alt="QRIS Dukungan Kopi" class="img-fluid rounded-3" style="max-width: 240px;">
                    </div>

                    <h6 class="fw-bold text-dark mb-1">Fikri M - AkuOnline</h6>
                    <small class="text-muted d-block">
                        <i class="fa-solid fa-heart text-danger me-1"></i> Terima kasih atas dukungan dan apresiasi Anda!
                    </small>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                    <button type="button" class="btn btn-sm btn-secondary btn-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @yield('scripts')
</body>
</html>
