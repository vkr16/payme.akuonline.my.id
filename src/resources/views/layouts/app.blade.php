<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PayMe - QRIS Split Bill Generator')</title>

    @php
        $defaultOgTitle = 'PayMe - Split Bill & Dynamic QRIS Generator';
        $defaultOgDesc = 'Bagi tagihan pesanan secara adil dan transparan dengan konversi QRIS statis ke dinamis serta pembagian proporsional.';
        $defaultOgImage = asset('images/qrlogo.png');
        if (str_contains($defaultOgImage, ':///') || !str_contains($defaultOgImage, '://')) {
            $baseUrl = rtrim(config('app.url', 'https://payme.akuonline.my.id'), '/');
            $defaultOgImage = $baseUrl . '/images/qrlogo.png';
        }
    @endphp

    <!-- Standard Primary Meta Tags -->
    <meta name="title" content="@yield('meta_title', $defaultOgTitle)">
    <meta name="description" content="@yield('meta_description', $defaultOgDesc)">

    <!-- Open Graph / WhatsApp / Facebook Link Preview -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PayMe Split Bill">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('meta_title', $defaultOgTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultOgDesc)">
    <meta property="og:image" content="@yield('meta_image', $defaultOgImage)">
    <meta property="og:image:secure_url" content="@yield('meta_image', $defaultOgImage)">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="600">
    <meta property="og:image:type" content="image/png">

    <!-- Twitter Card Link Preview -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('meta_title', $defaultOgTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $defaultOgDesc)">
    <meta name="twitter:image" content="@yield('meta_image', $defaultOgImage)">

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

            /* STANDARDIZED BORDER RADIUS DESIGN TOKENS SCALE */
            --radius-card: 1.25rem;       /* 20px - Cards, Modals, Major Containers, Dropzone */
            --radius-btn: 9999px;         /* Pill - All Buttons (Primary, Secondary, Stepper, Actions) */
            --radius-input: 0.75rem;      /* 12px - Form Controls, Selects, Input-group texts */
            --radius-badge: 9999px;      /* Pill - All Status Badges, Labels, Tags */
            --radius-box: 0.75rem;        /* 12px - Inner Alert boxes, Item Cards, List Rows */
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
            overflow-x: hidden;
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

        /* 1. CARDS, GLASS CARDS, MODALS & CONTAINERS */
        .card, .glass-card {
            border-radius: var(--radius-card) !important;
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
            border-top-left-radius: var(--radius-card) !important;
            border-top-right-radius: var(--radius-card) !important;
        }

        /* GLASSMORPHISM MODAL POPUPS */
        .modal-content {
            border-radius: var(--radius-card) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            background: rgba(255, 255, 255, 0.86) !important;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }

        .modal-backdrop.show {
            background-color: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .modal-header {
            background: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
            border-top-left-radius: var(--radius-card) !important;
            border-top-right-radius: var(--radius-card) !important;
        }

        .modal-footer {
            background: rgba(248, 250, 252, 0.5);
            border-top: 1px solid rgba(226, 232, 240, 0.7);
            border-bottom-left-radius: var(--radius-card) !important;
            border-bottom-right-radius: var(--radius-card) !important;
        }

        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            background: rgba(248, 250, 252, 0.8);
            border-radius: var(--radius-card) !important;
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

        /* 2. BUTTONS - Standardized Pill Scale */
        .btn, .btn-pill, .btn-rounded {
            border-radius: var(--radius-btn) !important;
        }

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

        /* 3. FORM INPUT FIELDS & SELECTS */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: var(--radius-input) !important;
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

        /* COMBINED ELEMENTS & INPUT GROUPS */
        .input-group {
            border-radius: var(--radius-input);
        }

        .input-group > .form-control,
        .input-group > .form-select,
        .input-group > .input-group-text,
        .input-group > .btn {
            border-radius: 0 !important;
        }

        .input-group > :first-child,
        .input-group > .form-control:first-child,
        .input-group > .form-select:first-child,
        .input-group > .input-group-text:first-child,
        .input-group > .btn:first-child {
            border-top-left-radius: var(--radius-input) !important;
            border-bottom-left-radius: var(--radius-input) !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .input-group > :last-child,
        .input-group > .form-control:last-child,
        .input-group > .form-select:last-child,
        .input-group > .input-group-text:last-child,
        .input-group > .btn:last-child {
            border-top-right-radius: var(--radius-input) !important;
            border-bottom-right-radius: var(--radius-input) !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .input-group > :not(:first-child):not(:last-child) {
            border-radius: 0 !important;
        }

        /* 4. BADGES - Standardized Pill Scale */
        .badge {
            border-radius: var(--radius-badge) !important;
        }

        /* 5. INNER BOXES, ALERTS & ITEM CARDS */
        .alert, .rounded-3, .item-claim-card {
            border-radius: var(--radius-box) !important;
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
            opacity: 0.4;
            max-width: 92vw;
            box-sizing: border-box;
        }

        .lunas-stamp-badge {
            border: 8px double #059669;
            color: #059669;
            padding: 1.25rem 3rem;
            border-radius: 1.5rem;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.1);
            background: rgba(255, 255, 255, 0.5);
            animation: stampBounce 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            max-width: 100%;
            box-sizing: border-box;
        }

        .lunas-stamp-inner span {
            font-size: 5rem;
            font-weight: 900;
            letter-spacing: 14px;
            line-height: 1;
            display: block;
            text-transform: uppercase;
        }

        @media (max-width: 576px) {
            .lunas-stamp-overlay {
                top: 210px;
                max-width: 95vw;
            }

            .lunas-stamp-badge {
                padding: 0.75rem 1.25rem;
                border-width: 5px;
            }

            .lunas-stamp-inner span {
                font-size: 2.8rem;
                letter-spacing: 6px;
            }

            .lunas-stamp-inner i {
                font-size: 2.2rem !important;
            }

            .lunas-stamp-inner small {
                font-size: 0.7rem !important;
                letter-spacing: 2px !important;
            }
        }

        @keyframes stampBounce {
            0% {
                transform: scale(2.5) rotate(-35deg);
                opacity: 0;
            }
            100% {
                transform: scale(1) rotate(-14deg);
                opacity: 0.4;
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
    <nav class="navbar navbar-expand-lg navbar-light navbar-glass sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('bills.create') }}">
                <img src="{{ asset('images/qrlogo.png') }}" alt="PayMe" width="38" height="38" class="d-inline-block rounded-3 brand-logo-glow">
                <div class="d-flex flex-column lh-1 ms-3">
                    <span class="fw-extrabold text-dark fs-4 mb-0 tracking-tight" style="letter-spacing: -0.03em;">PayMe</span>
                    <span class="text-muted fw-medium" style="font-size: 0.68rem; margin-top: 2px;">by AkuOnline</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 fw-semibold d-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#instantQrisModal">
                    <i class="fa-solid fa-bolt text-warning"></i>
                    <span>QR Instant</span>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 fw-semibold d-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">
                    <i class="fa-solid fa-shield-halved text-primary"></i>
                    <span class="d-none d-sm-inline">Privasi & Retensi Data</span>
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-bold btn-pill px-3 shadow-xs d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot text-dark"></i>
                    <span class="d-none d-md-inline">Traktir Kopi</span>
                </button>
                <a href="{{ route('bills.create') }}" class="btn btn-gradient-primary btn-sm btn-pill px-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span class="d-none d-sm-inline">Buat Patungan</span>
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
            <p class="mb-2 small font-medium">&copy; {{ date('Y') }} PayMe &bull; Solusi Patungan QRIS Statis ke Dinamis</p>

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

    <!-- PRIVACY POLICY & DATA RETENTION MODAL -->
    <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-modal border-0">
                <div class="modal-header border-bottom border-light py-3">
                    <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center" id="privacyPolicyModalLabel">
                        <i class="fa-solid fa-shield-halved text-primary me-2 fs-5"></i> Kebijakan Privasi & Masa Penyimpanan Data (Retensi)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4 text-dark">
                    <div class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 mb-4 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-user-shield text-primary fs-3 flex-shrink-0 mt-1"></i>
                        <div>
                            <h6 class="fw-bold text-primary mb-1">Komitmen Privasi & Keamanan PayMe</h6>
                            <p class="small text-muted mb-0">PayMe berkomitmen penuh untuk menjaga keamanan dan privasi data transaksi Anda. Kami tidak pernah menjual, menyewakan, atau membagikan informasi transaksi Anda kepada pihak ketiga mana pun.</p>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Ketentuan Retensi & Penghapusan Otomatis Data</h6>
                    <div class="card border mb-3 shadow-xs bg-white">
                        <div class="card-body p-3">
                            <div class="vstack gap-3">
                                <!-- Item 1: Tagihan Lunas -->
                                <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-success border-opacity-25">
                                    <div class="gap-2 mb-2">
                                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-circle-check text-success"></i>
                                            <span>Tagihan Lunas</span>
                                        </div>
                                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            Disimpan Max {{ config('payme.retention.paid_days', 3) }} Hari Kalender
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-0 lh-sm">Seluruh tagihan yang sudah terbayar lunas oleh anggota.</p>
                                </div>

                                <!-- Item 2: Tagihan Belum Lunas -->
                                <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-warning border-opacity-30">
                                    <div class="gap-2 mb-2">
                                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-clock text-warning"></i>
                                            <span>Tagihan Belum Lunas</span>
                                        </div>
                                        <div class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            Disimpan Max {{ config('payme.retention.unpaid_days', 7) }} Hari Kalender
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-0 lh-sm">Tagihan aktif yang belum terselesaikan sepenuhnya.</p>
                                </div>

                                <!-- Item 3: Jadwal Pembersihan -->
                                <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-danger border-opacity-25">
                                    <div class="gap-2 mb-2">
                                        <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-trash-can text-danger"></i>
                                            <span>Jadwal Pembersihan Otomatis</span>
                                        </div>
                                        <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                            Pukul 00:00 WIB Hari Selanjutnya
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-0 lh-sm">Pembersihan dan penghapusan permanen seluruh data dari server.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <p class="small text-muted mb-0">
                            <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                            Setiap data tagihan yang telah mencapai masa batas retensi di atas akan secara otomatis <strong>dihapus permanen dari database pada pukul 00:00 WIB</strong> di hari berikutnya. Kami menyarankan Anda menyimpan atau melakukan screenshot bukti pembayaran jika diperlukan untuk arsip pribadi.
                        </p>
                    </div>
                </div>
                <div class="modal-footer justify-content-end border-0 pt-0 pb-4 pe-4">
                    <button type="button" class="btn btn-sm btn-primary btn-pill px-4" data-bs-dismiss="modal">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- INSTANT DYNAMIC QRIS MODAL (QUICK ACCESS) -->
    <div class="modal fade" id="instantQrisModal" tabindex="-1" aria-labelledby="instantQrisModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-modal border-0 shadow-lg">
                <div class="modal-header border-bottom border-light py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-bolt-lightning text-warning fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark fs-6 mb-0 d-flex align-items-center gap-2" id="instantQrisModalLabel">
                                <span>Buat QR Dinamis Instant</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.68rem;">Quick Access</span>
                            </h5>
                            <small class="text-muted" style="font-size: 0.75rem;">Konversi QRIS statis jadi dinamis ber-nominal presisi tanpa perlu membuat tagihan/patungan.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <!-- Hidden Canvas for Decoding Uploaded QR -->
                    <canvas id="instantQrisCanvas" class="d-none"></canvas>

                    <div class="row g-4">
                        <!-- LEFT COLUMN: INPUT QRIS & NOMINAL -->
                        <div class="col-lg-6">
                            <!-- 1. QRIS SOURCE CARD -->
                            <div class="card border mb-3 shadow-xs bg-white">
                                <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-qrcode text-primary"></i> 1. Sumber QRIS Statis
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small d-none" id="instantSavedBadge">
                                        <i class="fa-solid fa-hard-drive me-1"></i> Tersimpan
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <!-- State A: Empty / Dropzone -->
                                    <div id="instantQrisEmptyState">
                                        <label class="upload-dropzone w-100 mb-2 py-3 px-2 text-center cursor-pointer" for="instantQrisFileInput" id="instantQrisDropzone">
                                            <i class="fa-solid fa-cloud-arrow-up text-primary fs-3 mb-2 d-block"></i>
                                            <span class="fw-bold text-dark d-block small mb-1">Unggah Gambar QRIS Statis</span>
                                            <span class="text-muted d-block" style="font-size: 0.72rem;">Drag & drop, pilih file, atau tekan <strong>Ctrl+V</strong></span>
                                            <input type="file" id="instantQrisFileInput" class="d-none" accept="image/*">
                                        </label>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" style="font-size: 0.72rem;" data-bs-toggle="collapse" data-bs-target="#instantManualStringCollapse">
                                                <i class="fa-solid fa-keyboard me-1"></i> Punya string QRIS teks? Masukkan manual
                                            </button>
                                        </div>
                                        <div class="collapse mt-2" id="instantManualStringCollapse">
                                            <div class="p-3 bg-light rounded-3 border">
                                                <textarea class="form-control form-control-sm font-monospace mb-2" id="instantManualStringInput" rows="2" placeholder="Tempel payload 000201..." style="font-size: 0.75rem;"></textarea>
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-pill w-100 py-1" id="btnApplyInstantManualString" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-check me-1"></i> Gunakan String QRIS
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- State B: Valid QRIS Detected with Thumbnail Preview -->
                                    <div id="instantQrisValidState" class="d-none">
                                        <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-success border-opacity-25">
                                                <span class="badge bg-success text-white px-2 py-1 fw-semibold" style="font-size: 0.68rem;">
                                                    <i class="fa-solid fa-circle-check me-1"></i> QRIS Valid
                                                </span>
                                                <button type="button" class="btn btn-link text-danger text-decoration-none p-0 small fw-semibold" id="btnChangeInstantQris" style="font-size: 0.75rem;">
                                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Ganti QRIS
                                                </button>
                                            </div>
                                            <!-- Thumbnail Preview & Merchant Details -->
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="flex-shrink-0 bg-white p-1 rounded-3 border shadow-xs d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                                                    <img id="instantQrisThumbnailImg" src="" alt="Thumbnail QRIS Statis" class="img-fluid rounded-2" style="max-width: 50px; max-height: 50px; object-fit: contain;">
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h6 class="fw-bold text-dark mb-0 fs-6 text-truncate" id="instantMerchantNameText">-</h6>
                                                    <small class="text-muted d-block text-truncate" id="instantMerchantCityText" style="font-size: 0.75rem;">-</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="instantRememberQrisCheckbox" checked>
                                            <label class="form-check-label text-muted small cursor-pointer" for="instantRememberQrisCheckbox" style="font-size: 0.75rem;">
                                                Ingat QRIS ini di peramban untuk pemakaian instan berikutnya
                                            </label>
                                        </div>
                                    </div>

                                    <!-- State C: Invalid QRIS Notice -->
                                    <div id="instantQrisInvalidState" class="d-none">
                                        <div class="p-3 rounded-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-center">
                                            <i class="fa-solid fa-circle-xmark text-danger fs-4 mb-1 d-block"></i>
                                            <h6 class="fw-bold text-danger mb-1 small" id="instantInvalidTitle">Format QRIS Tidak Valid</h6>
                                            <p class="text-muted mb-2" id="instantInvalidReason" style="font-size: 0.72rem;">Gambar bukan standar QRIS EMVCo Indonesia.</p>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-pill px-3 py-1" id="btnRetryInstantQris" style="font-size: 0.75rem;">
                                                Coba Gambar Lain
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. NOMINAL & BIAYA ADMIN -->
                            <div class="card border mb-3 shadow-xs bg-white">
                                <div class="card-header bg-light py-2 px-3">
                                    <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-money-bill-wave text-primary"></i> 2. Nominal & Biaya Layanan
                                    </span>
                                </div>
                                <div class="card-body p-3">
                                    <!-- Nominal Input -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-dark small mb-1">Nominal Pembayaran <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light fw-bold text-primary">Rp</span>
                                            <input type="text" inputmode="numeric" id="instantAmountInput" class="form-control form-control-lg fw-extrabold text-primary" placeholder="0" autocomplete="off">
                                        </div>
                                    </div>

                                    <!-- Quick Preset Nominal Chips -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small mb-1" style="font-size: 0.72rem;">Pilih Cepat:</label>
                                        <div class="d-flex flex-wrap gap-2" id="instantPresetContainer">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="10000" style="font-size: 0.75rem;">10rb</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="20000" style="font-size: 0.75rem;">20rb</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="50000" style="font-size: 0.75rem;">50rb</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="100000" style="font-size: 0.75rem;">100rb</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="200000" style="font-size: 0.75rem;">200rb</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 instant-preset-btn" data-amount="500000" style="font-size: 0.75rem;">500rb</button>
                                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-danger" id="btnInstantClearAmount" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Optional Fee Toggle -->
                                    <div class="border-top pt-3">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="instantToggleFeeSwitch">
                                            <label class="form-check-label text-muted small cursor-pointer" for="instantToggleFeeSwitch" style="font-size: 0.78rem;">
                                                Tambah Biaya Layanan / Admin (Opsional)
                                            </label>
                                        </div>

                                        <div class="collapse mt-3" id="instantFeeCollapse">
                                            <div class="p-3 bg-light rounded-3 border">
                                                <div class="row g-2">
                                                    <div class="col-5">
                                                        <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Jenis Biaya</label>
                                                        <select id="instantFeeTypeSelect" class="form-select form-select-sm">
                                                            <option value="r" selected>Rupiah (Rp)</option>
                                                            <option value="p">Persen (%)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-7">
                                                        <label class="form-label text-muted mb-1" style="font-size: 0.72rem;">Nilai Biaya</label>
                                                        <input type="number" id="instantFeeValueInput" class="form-control form-control-sm" placeholder="Contoh: 1000 atau 0.7" min="0" step="any">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <button type="button" class="btn btn-gradient-primary btn-lg btn-pill w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnTriggerGenerateInstant">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Cetak QRIS Dinamis</span>
                            </button>
                        </div>

                        <!-- RIGHT COLUMN: OUTPUT PREVIEW & ACTIONS -->
                        <div class="col-lg-6">
                            <div class="card border h-100 shadow-xs bg-white d-flex flex-column">
                                <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-circle-check text-success"></i> 3. Hasil QRIS Dinamis
                                    </span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small" id="instantDynamicTagBadge">
                                        Tag 54 Locked
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                                    <!-- Placeholder when not generated -->
                                    <div id="instantResultPlaceholder" class="py-5 text-muted">
                                        <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                            <i class="fa-solid fa-qrcode text-secondary fs-2"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">QRIS Dinamis Siap Dibuat</h6>
                                        <p class="small text-muted mb-0 px-3" style="font-size: 0.78rem;">
                                            Pilih gambar QRIS statis dan masukkan nominal pembayaran di sebelah kiri, lalu klik <strong>Cetak QRIS Dinamis</strong>.
                                        </p>
                                    </div>

                                    <!-- Result Card (shown when generated) -->
                                    <div id="instantResultCard" class="w-100 d-none">
                                        <div class="p-3 rounded-4 bg-light border mb-3 text-center">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-2 pb-2 border-bottom">
                                                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 text-truncate" style="max-width: 180px;">
                                                    <i class="fa-solid fa-store me-1"></i> <span id="instantResultMerchantDisplay">Merchant</span>
                                                </div>
                                                <small class="text-muted" id="instantResultCityDisplay" style="font-size: 0.72rem;">Indonesia</small>
                                            </div>

                                            <div class="mb-2">
                                                <span class="text-muted small d-block" style="font-size: 0.72rem;">TOTAL TAGIHAN</span>
                                                <h3 class="fw-extrabold text-primary mb-0" id="instantResultAmountDisplay" style="letter-spacing: -0.02em;">Rp 0</h3>
                                                <small class="text-muted" id="instantResultFeeSubtext" style="font-size: 0.72rem;"></small>
                                            </div>

                                            <!-- Rendered QR Code Container -->
                                            <div class="p-3 bg-white rounded-3 border d-inline-block shadow-xs mb-2" id="instantQrContainer"></div>

                                            <small class="text-muted d-block lh-sm" style="font-size: 0.72rem;">
                                                <i class="fa-solid fa-mobile-screen-button text-primary me-1"></i>
                                                Pindai pakai BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay, dll.
                                            </small>
                                        </div>

                                        <!-- Action Buttons Grid -->
                                        <div class="vstack gap-2">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-gradient-primary btn-sm btn-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs" id="btnDownloadInstantQr" title="Unduh QR Code siap scan m-banking">
                                                        <i class="fa-solid fa-qrcode"></i>
                                                        <span>Unduh QR</span>
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs" id="btnDownloadInstantCard" title="Unduh Kartu Pembayaran lengkap">
                                                        <i class="fa-solid fa-id-card"></i>
                                                        <span>Unduh Kartu</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs" id="btnCopyInstantQrImage">
                                                        <i class="fa-solid fa-copy"></i>
                                                        <span id="btnCopyInstantImageText">Salin Gambar</span>
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-pill w-100 py-2 small d-flex align-items-center justify-content-center gap-1" id="btnCopyInstantString">
                                                        <i class="fa-solid fa-code"></i>
                                                        <span id="btnCopyInstantStringText">Salin String QR</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <div>
                                                <a href="#" target="_blank" class="btn btn-outline-success btn-sm btn-pill w-100 py-2 small d-flex align-items-center justify-content-center gap-1" id="btnShareInstantWa">
                                                    <i class="fa-brands fa-whatsapp"></i>
                                                    <span>Kirim WhatsApp</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between bg-light bg-opacity-50 py-2 px-3 border-top">
                    <small class="text-muted" style="font-size: 0.72rem;">
                        <i class="fa-solid fa-lock text-success me-1"></i> Data QRIS diproses sepenuhnya di peramban Anda (Offline-ready & Privacy safe).
                    </small>
                    <button type="button" class="btn btn-sm btn-secondary btn-pill px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Global Instant Dynamic QRIS Engine -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Storage Keys
        const STORAGE_KEY_QRIS = 'payme_saved_qris';

        // Elements
        const instantQrisModal = document.getElementById('instantQrisModal');
        const instantQrisFileInput = document.getElementById('instantQrisFileInput');
        const instantQrisDropzone = document.getElementById('instantQrisDropzone');
        const instantQrisEmptyState = document.getElementById('instantQrisEmptyState');
        const instantQrisValidState = document.getElementById('instantQrisValidState');
        const instantQrisInvalidState = document.getElementById('instantQrisInvalidState');
        const instantMerchantNameText = document.getElementById('instantMerchantNameText');
        const instantMerchantCityText = document.getElementById('instantMerchantCityText');
        const instantQrisThumbnailImg = document.getElementById('instantQrisThumbnailImg');
        const instantInvalidReason = document.getElementById('instantInvalidReason');
        const instantRememberQrisCheckbox = document.getElementById('instantRememberQrisCheckbox');
        const instantSavedBadge = document.getElementById('instantSavedBadge');
        const instantQrisCanvas = document.getElementById('instantQrisCanvas');

        const instantManualStringInput = document.getElementById('instantManualStringInput');
        const btnApplyInstantManualString = document.getElementById('btnApplyInstantManualString');
        const btnChangeInstantQris = document.getElementById('btnChangeInstantQris');
        const btnRetryInstantQris = document.getElementById('btnRetryInstantQris');

        const instantAmountInput = document.getElementById('instantAmountInput');
        const instantPresetContainer = document.getElementById('instantPresetContainer');
        const btnInstantClearAmount = document.getElementById('btnInstantClearAmount');

        const instantToggleFeeSwitch = document.getElementById('instantToggleFeeSwitch');
        const instantFeeCollapse = document.getElementById('instantFeeCollapse');
        const instantFeeTypeSelect = document.getElementById('instantFeeTypeSelect');
        const instantFeeValueInput = document.getElementById('instantFeeValueInput');

        const btnTriggerGenerateInstant = document.getElementById('btnTriggerGenerateInstant');
        const instantResultPlaceholder = document.getElementById('instantResultPlaceholder');
        const instantResultCard = document.getElementById('instantResultCard');
        const instantQrContainer = document.getElementById('instantQrContainer');
        const instantResultMerchantDisplay = document.getElementById('instantResultMerchantDisplay');
        const instantResultCityDisplay = document.getElementById('instantResultCityDisplay');
        const instantResultAmountDisplay = document.getElementById('instantResultAmountDisplay');
        const instantResultFeeSubtext = document.getElementById('instantResultFeeSubtext');

        const btnDownloadInstantQr = document.getElementById('btnDownloadInstantQr');
        const btnDownloadInstantCard = document.getElementById('btnDownloadInstantCard');
        const btnCopyInstantQrImage = document.getElementById('btnCopyInstantQrImage');
        const btnCopyInstantImageText = document.getElementById('btnCopyInstantImageText');
        const btnCopyInstantString = document.getElementById('btnCopyInstantString');
        const btnCopyInstantStringText = document.getElementById('btnCopyInstantStringText');
        const btnShareInstantWa = document.getElementById('btnShareInstantWa');

        let currentInstantPayload = '';
        let currentDynamicPayload = '';
        let currentMerchantInfo = { name: 'Merchant', city: 'Indonesia' };
        let currentThumbnailUrl = '';
        let currentFinalPayable = 0;

        // 1. Initial LocalStorage Check
        loadSavedQris();

        function loadSavedQris() {
            try {
                const saved = localStorage.getItem(STORAGE_KEY_QRIS);
                if (saved) {
                    const data = JSON.parse(saved);
                    if (data && data.payload) {
                        const validation = validateQrisPayload(data.payload);
                        if (validation.valid) {
                            setValidQrisState(data.payload, validation.merchantName, validation.location, data.thumbnail || '', true);
                            return true;
                        }
                    }
                }
            } catch (e) {
                console.error('Error loading saved QRIS', e);
            }
            return false;
        }

        // 2. Validate QRIS Payload String
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
            const location = locMatch ? locMatch[2].substring(0, parseInt(locMatch[1], 10)).trim() : 'Indonesia';

            return {
                valid: true,
                merchantName: merchantName,
                location: location
            };
        }

        // 3. Set Valid / Invalid UI States
        function setValidQrisState(payload, merchantName, location, thumbnailSrc = '', fromSaved = false) {
            currentInstantPayload = payload;
            currentMerchantInfo = {
                name: merchantName || 'Merchant QRIS',
                city: location || 'Indonesia'
            };
            currentThumbnailUrl = thumbnailSrc;

            instantMerchantNameText.innerText = currentMerchantInfo.name;
            instantMerchantCityText.innerText = currentMerchantInfo.city ? 'Lokasi: ' + currentMerchantInfo.city : '';

            // Render thumbnail preview
            if (instantQrisThumbnailImg) {
                if (thumbnailSrc) {
                    instantQrisThumbnailImg.src = thumbnailSrc;
                } else {
                    // Generate fallback mini QR preview from payload
                    try {
                        const tempDiv = document.createElement('div');
                        new QRCode(tempDiv, { text: payload, width: 80, height: 80, correctLevel: QRCode.CorrectLevel.L });
                        setTimeout(() => {
                            const c = tempDiv.querySelector('canvas') || tempDiv.querySelector('img');
                            if (c) {
                                currentThumbnailUrl = c.toDataURL ? c.toDataURL() : c.src;
                                instantQrisThumbnailImg.src = currentThumbnailUrl;
                            }
                        }, 50);
                    } catch (err) {}
                }
            }

            instantQrisEmptyState.classList.add('d-none');
            instantQrisInvalidState.classList.add('d-none');
            instantQrisValidState.classList.remove('d-none');

            if (fromSaved && instantSavedBadge) {
                instantSavedBadge.classList.remove('d-none');
            }

            if (instantRememberQrisCheckbox && instantRememberQrisCheckbox.checked) {
                try {
                    localStorage.setItem(STORAGE_KEY_QRIS, JSON.stringify({
                        payload: payload,
                        merchantName: currentMerchantInfo.name,
                        merchantCity: currentMerchantInfo.city,
                        thumbnail: currentThumbnailUrl,
                        updated_at: new Date().toISOString()
                    }));
                } catch (e) {}
            }
        }

        function setInvalidQrisState(reason) {
            currentInstantPayload = '';
            currentThumbnailUrl = '';
            instantQrisEmptyState.classList.add('d-none');
            instantQrisValidState.classList.add('d-none');
            instantQrisInvalidState.classList.remove('d-none');
            instantInvalidReason.innerText = reason || 'Kode QR tidak dikenali sebagai format QRIS standar.';
        }

        function resetQrisState() {
            currentInstantPayload = '';
            currentThumbnailUrl = '';
            instantQrisValidState.classList.add('d-none');
            instantQrisInvalidState.classList.add('d-none');
            instantQrisEmptyState.classList.remove('d-none');
            if (instantQrisFileInput) instantQrisFileInput.value = '';
            if (instantManualStringInput) instantManualStringInput.value = '';
            if (instantSavedBadge) instantSavedBadge.classList.add('d-none');
            if (instantQrisThumbnailImg) instantQrisThumbnailImg.src = '';
        }

        if (btnChangeInstantQris) {
            btnChangeInstantQris.addEventListener('click', resetQrisState);
        }

        if (btnRetryInstantQris) {
            btnRetryInstantQris.addEventListener('click', resetQrisState);
        }

        // 4. File Processing & Decoding using jsQR
        function processInstantImageFile(file) {
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    instantQrisCanvas.width = img.width;
                    instantQrisCanvas.height = img.height;
                    const ctx = instantQrisCanvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, img.width, img.height);

                    // Create compact thumbnail
                    let thumbDataUrl = '';
                    try {
                        const thumbCanvas = document.createElement('canvas');
                        thumbCanvas.width = 120;
                        thumbCanvas.height = 120;
                        const tCtx = thumbCanvas.getContext('2d');
                        tCtx.drawImage(img, 0, 0, 120, 120);
                        thumbDataUrl = thumbCanvas.toDataURL('image/png');
                    } catch (e) {}

                    const imageData = ctx.getImageData(0, 0, instantQrisCanvas.width, instantQrisCanvas.height);
                    if (typeof jsQR !== 'undefined') {
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        if (code && code.data) {
                            const validation = validateQrisPayload(code.data);
                            if (validation.valid) {
                                setValidQrisState(code.data, validation.merchantName, validation.location, thumbDataUrl, false);
                            } else {
                                setInvalidQrisState(validation.reason);
                            }
                        } else {
                            setInvalidQrisState('Tidak dapat mendeteksi kode QR dari gambar. Pastikan gambar jelas dan tidak blur.');
                        }
                    } else {
                        setInvalidQrisState('Library pembaca QR (jsQR) belum siap.');
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }

        if (instantQrisFileInput) {
            instantQrisFileInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    processInstantImageFile(e.target.files[0]);
                }
            });
        }

        // Drag & Drop
        if (instantQrisDropzone) {
            ['dragenter', 'dragover'].forEach(name => {
                instantQrisDropzone.addEventListener(name, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    instantQrisDropzone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach(name => {
                instantQrisDropzone.addEventListener(name, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    instantQrisDropzone.classList.remove('dragover');
                });
            });
            instantQrisDropzone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    processInstantImageFile(e.dataTransfer.files[0]);
                }
            });
        }

        // Paste (Ctrl+V) handler
        window.addEventListener('paste', function(e) {
            // Only process paste if modal is visible
            if (!instantQrisModal || !instantQrisModal.classList.contains('show')) return;

            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.indexOf('image/') !== -1) {
                    const blob = item.getAsFile();
                    processInstantImageFile(blob);
                    break;
                } else if (item.kind === 'string' && item.type === 'text/plain') {
                    item.getAsString(function(text) {
                        if (text && text.trim().startsWith('000201')) {
                            const validation = validateQrisPayload(text);
                            if (validation.valid) {
                                setValidQrisState(text, validation.merchantName, validation.location, '', false);
                            }
                        }
                    });
                }
            }
        });

        // Apply Manual String Input
        if (btnApplyInstantManualString && instantManualStringInput) {
            btnApplyInstantManualString.addEventListener('click', function() {
                const val = instantManualStringInput.value.trim();
                const validation = validateQrisPayload(val);
                if (validation.valid) {
                    setValidQrisState(val, validation.merchantName, validation.location, '', false);
                } else {
                    alert(validation.reason || 'String QRIS tidak valid.');
                }
            });
        }


        // 5. Currency Formatting Helper
        function formatRupiah(num) {
            return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
        }

        function parseRawAmount(str) {
            if (!str) return 0;
            const clean = String(str).replace(/[^\d]/g, '');
            return parseInt(clean, 10) || 0;
        }

        if (instantAmountInput) {
            instantAmountInput.addEventListener('input', function(e) {
                const raw = parseRawAmount(e.target.value);
                e.target.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
            });
        }

        // Presets buttons
        if (instantPresetContainer) {
            instantPresetContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.instant-preset-btn');
                if (!btn) return;
                const amt = parseInt(btn.dataset.amount, 10) || 0;
                if (amt > 0 && instantAmountInput) {
                    instantAmountInput.value = amt.toLocaleString('id-ID');
                }
            });
        }

        if (btnInstantClearAmount && instantAmountInput) {
            btnInstantClearAmount.addEventListener('click', function() {
                instantAmountInput.value = '';
                instantAmountInput.focus();
            });
        }

        // Fee toggle collapse
        if (instantToggleFeeSwitch && instantFeeCollapse) {
            instantToggleFeeSwitch.addEventListener('change', function() {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(instantFeeCollapse, { toggle: false });
                if (this.checked) {
                    bsCollapse.show();
                } else {
                    bsCollapse.hide();
                }
            });
        }

        // 6. Dynamic QRIS Converter Engine
        function convertToDynamicQRIS(qris, nominal, useServiceFee, feeType, feeValue) {
            let tax = '';

            if (useServiceFee && feeValue && parseFloat(feeValue) > 0) {
                const feeValStr = String(feeValue).trim();
                const feeLen = String(feeValStr.length).padStart(2, '0');
                tax = feeType === 'r'
                    ? "55020256" + feeLen + feeValStr
                    : "55020357" + feeLen + feeValStr;
            }

            // Remove trailing 4-char CRC if present
            let cleanQris = qris.trim();
            if (cleanQris.length > 4 && /^[0-9A-Fa-f]{4}$/.test(cleanQris.slice(-4))) {
                cleanQris = cleanQris.slice(0, -4);
            }

            // Replace Tag 010211 (Static) with 010212 (Dynamic)
            cleanQris = cleanQris.replace("010211", "010212");

            const parts = cleanQris.split("5802ID");
            if (parts.length < 2) {
                return qris; // Fallback
            }

            const prefix = parts[0];
            const suffix = parts[1];

            const nominalStr = String(Math.round(nominal));
            const nominalData = "54" + String(nominalStr.length).padStart(2, '0') + nominalStr;

            const payloadToCrc = `${prefix}${nominalData}${tax || ''}5802ID${suffix}`;
            const crc = calculateCRC16(payloadToCrc);

            return payloadToCrc + crc;
        }

        function calculateCRC16(str) {
            let crc = 0xFFFF;
            for (let c = 0; c < str.length; c++) {
                crc ^= str.charCodeAt(c) << 8;
                for (let i = 0; i < 8; i++) {
                    crc = (crc & 0x8000) ? ((crc << 1) ^ 0x1021) & 0xFFFF : (crc << 1) & 0xFFFF;
                }
            }
            return (crc & 0xFFFF).toString(16).toUpperCase().padStart(4, '0');
        }

        // 7. Generate Action Handler
        if (btnTriggerGenerateInstant) {
            btnTriggerGenerateInstant.addEventListener('click', function() {
                if (!currentInstantPayload) {
                    alert('Harap unggah atau masukkan QRIS statis terlebih dahulu!');
                    return;
                }

                const baseNominal = parseRawAmount(instantAmountInput ? instantAmountInput.value : '');
                if (!baseNominal || baseNominal <= 0) {
                    alert('Harap masukkan nominal pembayaran yang valid!');
                    if (instantAmountInput) instantAmountInput.focus();
                    return;
                }

                const useFee = instantToggleFeeSwitch ? instantToggleFeeSwitch.checked : false;
                const feeType = instantFeeTypeSelect ? instantFeeTypeSelect.value : 'r';
                const feeValue = instantFeeValueInput ? parseFloat(instantFeeValueInput.value) || 0 : 0;

                let totalAmount = baseNominal;
                let feeText = '';

                if (useFee && feeValue > 0) {
                    if (feeType === 'r') {
                        totalAmount += feeValue;
                        feeText = `Termasuk biaya admin +${formatRupiah(feeValue)}`;
                    } else if (feeType === 'p') {
                        const calculatedFee = Math.round(baseNominal * (feeValue / 100));
                        totalAmount += calculatedFee;
                        feeText = `Termasuk biaya layanan ${feeValue}% (+${formatRupiah(calculatedFee)})`;
                    }
                }

                currentFinalPayable = totalAmount;

                // Build Dynamic QRIS Payload
                currentDynamicPayload = convertToDynamicQRIS(
                    currentInstantPayload,
                    baseNominal,
                    useFee,
                    feeType,
                    feeValue > 0 ? String(feeValue) : ''
                );

                // Render Dynamic QR
                instantResultMerchantDisplay.innerText = currentMerchantInfo.name;
                instantResultCityDisplay.innerText = currentMerchantInfo.city;
                instantResultAmountDisplay.innerText = formatRupiah(totalAmount);
                instantResultFeeSubtext.innerText = feeText;

                instantQrContainer.innerHTML = '';
                if (typeof QRCode !== 'undefined') {
                    renderQrWithQuietZone(instantQrContainer, currentDynamicPayload, {
                        qrSize: 320,
                        margin: 20,
                        displaySize: '220px'
                    });
                }

                // Update Share Link
                const waText = encodeURIComponent(
                    `Halo, berikut QRIS Dinamis sebesar *${formatRupiah(totalAmount)}* untuk pembayaran ke *${currentMerchantInfo.name}*.\n\nNominal pembayaran sudah terkunci otomatis di QR Code ini. Silakan scan melalui Mobile Banking (BCA, Mandiri, BRI, dll) atau E-Wallet (GoPay, OVO, DANA, ShopeePay).`
                );
                if (btnShareInstantWa) {
                    btnShareInstantWa.href = `https://api.whatsapp.com/send?text=${waText}`;
                }

                // Toggle Result View
                instantResultPlaceholder.classList.add('d-none');
                instantResultCard.classList.remove('d-none');
            });
        }

        // 8a. Download Pure QR Code with Quiet Zone (Optimized for m-banking scanners)
        if (btnDownloadInstantQr) {
            btnDownloadInstantQr.addEventListener('click', function() {
                if (!currentDynamicPayload) return;
                const qrCanvas = instantQrContainer.querySelector('canvas');
                const qrImg = instantQrContainer.querySelector('img');

                let dataUrl = '';
                if (qrCanvas) {
                    dataUrl = qrCanvas.toDataURL('image/png');
                } else if (qrImg) {
                    dataUrl = qrImg.src;
                }

                if (dataUrl) {
                    const link = document.createElement('a');
                    const safeName = (currentMerchantInfo.name || 'merchant').toLowerCase().replace(/[^a-z0-9]/g, '-');
                    link.download = `qris-dinamis-${safeName}-${currentFinalPayable}.png`;
                    link.href = dataUrl;
                    link.click();
                } else {
                    alert('Gambar QR Code tidak tersedia.');
                }
            });
        }

        // 8b. Download Branded High-Resolution PNG Card
        if (btnDownloadInstantCard) {
            btnDownloadInstantCard.addEventListener('click', function() {
                if (!currentDynamicPayload) return;
                generateQrCardCanvas(function(canvas) {
                    const link = document.createElement('a');
                    const safeName = (currentMerchantInfo.name || 'merchant').toLowerCase().replace(/[^a-z0-9]/g, '-');
                    link.download = `kartu-qris-${safeName}-${currentFinalPayable}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            });
        }

        // 9. Copy QR Image to Clipboard (Pure QR with Quiet Zone)
        if (btnCopyInstantQrImage) {
            btnCopyInstantQrImage.addEventListener('click', function() {
                if (!currentDynamicPayload) return;
                const qrCanvas = instantQrContainer.querySelector('canvas');
                if (qrCanvas) {
                    qrCanvas.toBlob(function(blob) {
                        if (!blob) {
                            alert('Gagal membuat gambar QRIS.');
                            return;
                        }
                        if (navigator.clipboard && navigator.clipboard.write && typeof ClipboardItem !== 'undefined') {
                            navigator.clipboard.write([
                                new ClipboardItem({ 'image/png': blob })
                            ]).then(function() {
                                const orig = btnCopyInstantImageText.innerText;
                                btnCopyInstantImageText.innerText = 'Tersalin!';
                                setTimeout(() => { btnCopyInstantImageText.innerText = orig; }, 2000);
                            }).catch(function(err) {
                                alert('Peramban Anda tidak mengizinkan salin gambar langsung. Silakan gunakan tombol Unduh QR.');
                            });
                        } else {
                            alert('Fitur salin gambar langsung tidak didukung pada peramban ini. Silakan gunakan tombol Unduh QR.');
                        }
                    }, 'image/png');
                }
            });
        }

        // 10. Copy String Payload
        if (btnCopyInstantString) {
            btnCopyInstantString.addEventListener('click', function() {
                if (!currentDynamicPayload) return;
                navigator.clipboard.writeText(currentDynamicPayload).then(function() {
                    const orig = btnCopyInstantStringText.innerText;
                    btnCopyInstantStringText.innerText = 'String Tersalin!';
                    setTimeout(() => { btnCopyInstantStringText.innerText = orig; }, 2000);
                });
            });
        }

        /**
         * Helper to render QR code with an official EMVCo / ISO 18004 quiet zone (pure white margin).
         * Guarantees that mobile banking apps can scan the downloaded or saved image from gallery.
         */
        function renderQrWithQuietZone(container, payload, options = {}) {
            const qrSize = options.qrSize || 320;
            const margin = options.margin !== undefined ? options.margin : 20; // 20px white margin
            const displaySize = options.displaySize || '220px';

            container.innerHTML = '';

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

            const totalSize = qrSize + (margin * 2);
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = totalSize;
            finalCanvas.height = totalSize;
            const ctx = finalCanvas.getContext('2d');

            // 1. Fill solid pure white background across entire canvas
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, totalSize, totalSize);

            // 2. Draw raw QR code centered, leaving margin on all four sides
            ctx.imageSmoothingEnabled = false;
            ctx.drawImage(rawSource, margin, margin, qrSize, qrSize);

            // 3. Create display img with margin baked into data URL
            const finalImg = document.createElement('img');
            finalImg.src = finalCanvas.toDataURL('image/png');
            finalImg.alt = 'QRIS Dinamis';
            finalImg.className = 'img-fluid d-block mx-auto';
            finalImg.style.maxWidth = displaySize;
            finalImg.style.width = '100%';
            finalImg.style.height = 'auto';

            finalCanvas.style.display = 'none';
            container.appendChild(finalCanvas);
            container.appendChild(finalImg);
        }

        // 11. Helper to render aesthetic High-Res Branded Card on Canvas
        function generateQrCardCanvas(callback) {
            const qrCanvas = instantQrContainer.querySelector('canvas');
            const qrImg = instantQrContainer.querySelector('img');

            let qrSource = qrCanvas || qrImg;
            if (!qrSource) return;

            const width = 640;
            const height = 820;
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            // Draw Background with subtle shadow/gradient
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);

            // Top Branded Banner
            const gradient = ctx.createLinearGradient(0, 0, width, 0);
            gradient.addColorStop(0, '#0284c7');
            gradient.addColorStop(1, '#0d9488');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, width, 120);

            // Header Title
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 24px Plus Jakarta Sans, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('PayMe • QRIS DINAMIS INSTANT', width / 2, 48);

            ctx.font = '500 15px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
            ctx.fillText('Pembayaran Aman & Nominal Terkunci Otomatis', width / 2, 80);

            // Merchant Info Box
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 26px Plus Jakarta Sans, sans-serif';
            ctx.fillText(currentMerchantInfo.name || 'Merchant QRIS', width / 2, 175);

            ctx.fillStyle = '#64748b';
            ctx.font = '500 15px Plus Jakarta Sans, sans-serif';
            ctx.fillText(currentMerchantInfo.city || 'Indonesia', width / 2, 205);

            // Nominal
            ctx.fillStyle = '#0284c7';
            ctx.font = '800 36px Plus Jakarta Sans, sans-serif';
            ctx.fillText(formatRupiah(currentFinalPayable), width / 2, 260);

            // QR Border Card with pure white background
            const qrBoxSize = 340;
            const qrBoxX = (width - qrBoxSize) / 2;
            const qrBoxY = 295;

            ctx.fillStyle = '#ffffff'; // Solid pure white for quiet zone
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 2;
            roundRect(ctx, qrBoxX, qrBoxY, qrBoxSize, qrBoxSize, 20, true, true);

            // Draw QR Image (qrSource already contains quiet zone margin)
            const qrInnerSize = 300;
            const qrInnerX = (width - qrInnerSize) / 2;
            const qrInnerY = qrBoxY + (qrBoxSize - qrInnerSize) / 2;
            ctx.drawImage(qrSource, qrInnerX, qrInnerY, qrInnerSize, qrInnerSize);

            // Footer info
            ctx.fillStyle = '#475569';
            ctx.font = '600 14px Plus Jakarta Sans, sans-serif';
            ctx.fillText('Scan via BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay', width / 2, 690);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '400 13px Plus Jakarta Sans, sans-serif';
            ctx.fillText('Dibuat melalui PayMe • payme.akuonline.my.id', width / 2, 750);

            callback(canvas);
        }

        function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
            if (fill) ctx.fill();
            if (stroke) ctx.stroke();
        }

        // 12. Auto Open Modal on Query Param (?instant=1 or #instant-qris)
        if (window.location.search.includes('instant=1') || window.location.hash === '#instant-qris') {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(instantQrisModal);
            modalInstance.show();
        }
    });
    </script>

    @yield('scripts')
</body>
</html>

