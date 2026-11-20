<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &bull; Canteen Dawn</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CDN CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            background-color: #0f172a;
            min-height: 100vh;
        }
        .auth-card {
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-color: #ffffff;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3 py-5">

<div class="container" style="max-width: 480px;">
    <div class="card auth-card p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center p-2 mb-2">
                <svg width="42" height="42" viewBox="0 0 64 64" fill="none">
                    <rect width="64" height="64" rx="16" fill="#0f172a"/>
                    <path d="M42 22C39.5 19.5 36 18 32 18C24.268 18 18 24.268 18 32C18 39.732 24.268 46 32 46C36 46 39.5 44.5 42 42" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round"/>
                    <circle cx="33" cy="32" r="3.5" fill="#38bdf8"/>
                    <line x1="33" y1="25" x2="33" y2="28" stroke="#38bdf8" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h4 class="fw-bold text-dark mb-0 tracking-tight">CASHIER REGISTRATION</h4>
            <p class="text-muted small mb-0">Create credentials in shared app1.users</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger small py-2 mb-3 border-0 bg-danger-subtle text-danger" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <div class="row g-2 mb-3">
                <div class="col-sm-5">
                    <label for="idno" class="tracking-wide-caps mb-1 d-block">ID No. <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control tabular-nums @error('idno') is-invalid @enderror" 
                           id="idno" 
                           name="idno" 
                           value="{{ old('idno') }}" 
                           placeholder="e.g. 1002" 
                           required>
                </div>
                <div class="col-sm-7">
                    <label for="name" class="tracking-wide-caps mb-1 d-block">Full Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="e.g. Juan Dela Cruz" 
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="tracking-wide-caps mb-1 d-block">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-person"></i></span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           placeholder="Choose username" 
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="tracking-wide-caps mb-1 d-block">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" 
                           class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Min. 6 characters" 
                           required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="tracking-wide-caps mb-1 d-block">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" 
                           class="form-control border-start-0 ps-0" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Re-enter password" 
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2">
                Register Cashier Account
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center">
            <span class="text-muted small">Already registered?</span>
            <a href="{{ route('login') }}" class="text-dark fw-semibold small text-decoration-none ms-1">Sign in here &rarr;</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
