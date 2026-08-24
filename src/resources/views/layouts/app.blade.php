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
                <button type="button" class="btn btn-outline-primary btn-sm btn-pill px-3 fw-semibold d-flex align-items-center gap-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">
                    <i class="fa-solid fa-shield-halved text-primary"></i>
                    <span class="d-none d-sm-inline">Privasi & Retensi Data</span>
                </button>
                <button type="button" class="btn btn-warning btn-sm fw-bold btn-pill px-3 shadow-xs d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fa-solid fa-mug-hot text-dark"></i>
                    <span class="d-none d-md-inline">Traktir Kopi</span>
                </button>
                <a href="{{ route('bills.create') }}" class="btn btn-gradient-primary btn-sm btn-pill px-3.5 d-flex align-items-center gap-2">
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

                    <h6 class="fw-bold text-dark mb-2.5"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Ketentuan Retensi & Penghapusan Otomatis Data</h6>
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
                                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
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
                                        <div class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
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
                                        <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 fw-semibold" style="font-size: 0.78rem;">
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

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @yield('scripts')
</body>
</html>
