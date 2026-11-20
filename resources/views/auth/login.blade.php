<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In &bull; Inventory Dawn System</title>

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
            border-radius: 14px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-color: #ffffff;
        }
        .account-chip {
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .account-chip:hover {
            border-color: #0284c7;
            background: #f0f9ff;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3 py-5">

<div class="container" style="max-width: 460px;">
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-white mb-3" style="border-left: 3px solid #059669 !important;" role="alert">
            <div class="small text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 bg-white mb-3" style="border-left: 3px solid #0284c7 !important;" role="alert">
            <div class="small text-dark">{{ session('info') }}</div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card auth-card p-4 p-sm-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center p-2 mb-2">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <h4 class="fw-bold text-dark mb-0 tracking-tight">INVENTORY DAWN</h4>
            <p class="text-muted small mb-0">Integrated Inventory Management System (Group 2)</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger small py-2 border-0 bg-danger-subtle text-danger mb-3" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="username" class="tracking-wide-caps mb-1 d-block text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-person"></i></span>
                    <input type="text" 
                           class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           placeholder="e.g. mmanlaran" 
                           required 
                           autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="tracking-wide-caps mb-1 d-block text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem;">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" 
                           class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Enter password" 
                           required>
                </div>
                <div class="form-text small text-muted">Default system password: <code class="text-dark">password123</code></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2 mb-3">
                <i class="bi bi-box-arrow-in-right"></i> Sign In to Inventory Hub
            </button>
        </form>

        <!-- Quick 1-Click Evaluation Switcher -->
        <div class="pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">1-Click Member Logins:</span>
                <span class="badge bg-info-subtle text-info border small">Group 2 Roster</span>
            </div>

            <div class="d-flex flex-column gap-1.5" style="max-height: 220px; overflow-y: auto;">
                @foreach (\App\Models\InventoryUser::where('is_active', true)->where('username', '!=', 'canteen_system')->orderBy('id')->get() as $iu)
                    <form method="POST" action="{{ route('login.quick') }}" class="d-inline m-0">
                        @csrf
                        <input type="hidden" name="username" value="{{ $iu->username }}">
                        <button type="submit" class="account-chip w-100 text-start p-2 rounded d-flex justify-content-between align-items-center text-dark text-decoration-none">
                            <div>
                                <div class="fw-semibold small lh-1 text-dark">{{ $iu->name }}</div>
                                <small class="text-muted font-monospace" style="font-size: 0.7rem;">user: {{ $iu->username }}</small>
                            </div>
                            <span class="badge bg-info-subtle text-info small" style="font-size: 0.65rem;">{{ ucfirst($iu->role) }} &rarr;</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
