<div>
    <section class="py-5 my-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="section-header align-center">
                        <h2 class="section-title">Đăng ký tài khoản</h2>
                    </div>

                    <form wire:submit="register" class="bg-light p-5 rounded">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input wire:model="password_confirmation" type="password" class="form-control" id="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Đăng ký</button>
                        
                        <div class="text-center">
                            <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
