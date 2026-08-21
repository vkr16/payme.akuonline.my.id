<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PayMe - QRIS Split Bill Generator')</title>

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
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary fs-4" href="{{ route('bills.create') }}">
                <i class="fa-solid fa-qrcode"></i>
                <span>PayMe</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('bills.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i> Buat Patungan Baru
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
            <p class="mb-0 small">&copy; {{ date('Y') }} PayMe &bull; QRIS Split Bill Utility</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @yield('scripts')
</body>
</html>
