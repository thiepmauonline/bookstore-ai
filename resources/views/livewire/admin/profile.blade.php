<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading mb-4">
            <h1 class="h3 mb-1">Hồ sơ cá nhân</h1>
            <p class="text-muted">Quản lý thông tin tài khoản và bảo mật</p>
        </div>

        <div class="row">
            <div class="col-md-8 col-lg-6">
                <!-- Profile Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 fw-bold">Thông tin chung</div>
                    <div class="card-body">
                        @if (session()->has('profile_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('profile_message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit="updateProfile">
                            <div class="mb-3">
                                <label class="form-label">Email đăng nhập</label>
                                <input wire:model="email" type="email" class="form-control bg-light" disabled>
                                <div class="form-text text-muted">Email không thể thay đổi để đảm bảo bảo mật.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Số điện thoại</label>
                                <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="updateProfile">Lưu thay đổi</span>
                                    <span wire:loading wire:target="updateProfile">Đang lưu...</span>
                                </button>
                                
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key"></i> Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div wire:ignore.self class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="updatePassword">
                    <div class="modal-header">
                        <h5 class="modal-title">Đổi mật khẩu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if (session()->has('password_message'))
                            <div class="alert alert-success">{{ session('password_message') }}</div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <input wire:model="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input wire:model="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror">
                            @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input wire:model="new_password_confirmation" type="password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="updatePassword">Đổi mật khẩu</span>
                            <span wire:loading wire:target="updatePassword">Đang xử lý...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', (data) => {
                let modalEl = document.getElementById(data.modalId);
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
</div>
