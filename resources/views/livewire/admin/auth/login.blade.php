<section class="auth-card">
    <a class="auth-brand" href="{{ route('home') }}">
        <span class="brand-icon"><i class="bi bi-book-fill" aria-hidden="true"></i></span>
        <span><strong>Bookstore AI</strong><small>Đăng nhập quản trị</small></span>
    </a>
    
    <div class="auth-visual">
        <img src="{{ asset('assets/admin/images/png/dasher-ui-bootstrap-5.jpg') }}" alt="Bookstore dashboard interface">
    </div>

    <form wire:submit="login" class="needs-validation">
        <div class="mb-4">
            <p class="eyebrow mb-1">Bảo mật</p>
            <h1 class="h3 mb-1">Đăng nhập</h1>
            <p class="text-muted mb-0">Truy cập vào không gian quản trị của bạn.</p>
        </div>

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label" for="loginEmail">Email</label>
            <input wire:model="email" class="form-control @error('email') is-invalid @enderror" id="loginEmail" type="email" required autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="loginPassword">Mật khẩu</label>
            </div>
            <input wire:model="password" class="form-control @error('password') is-invalid @enderror" id="loginPassword" type="password" minlength="6" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-4">
            <input wire:model="remember" class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Nhớ mật khẩu</label>
        </div>

        <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Đăng nhập
        </button>
    </form>
</section>
