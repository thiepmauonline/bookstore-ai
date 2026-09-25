<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý người dùng</h2>
        <button wire:click="create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm người dùng
        </button>
    </div>

    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           class="form-control" placeholder="Tìm kiếm theo tên, email, số điện thoại...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="roleFilter" class="form-select">
                        <option value="">Tất cả vai trò</option>
                        <option value="user">Khách hàng</option>
                        <option value="admin">Quản trị viên</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @else
                                        <span class="badge bg-primary">Khách hàng</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Đã khóa</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-nowrap">
                                    <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="toggleStatus({{ $user->id }})"
                                            wire:confirm="{{ $user->status === 'active' ? 'Khóa tài khoản này? Khách sẽ không đăng nhập được.' : 'Mở khóa tài khoản này?' }}"
                                            class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                        <i class="bi {{ $user->status === 'active' ? 'bi-lock' : 'bi-unlock' }}"></i>
                                    </button>
                                    <button wire:click="delete({{ $user->id }})" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Không tìm thấy người dùng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div x-data="{ show: $wire.dispatch('show-modal') }" 
         x-show="show" 
         x-transition 
         class="modal fade" 
         style="display: none;"
         @keydown.escape.window="$wire.dispatch('hide-modal')">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditMode ? 'Cập nhật người dùng' : 'Thêm người dùng mới' }}</h5>
                    <button type="button" wire:click="$dispatch('hide-modal')" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <label class="form-label">Tên <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control">
                            @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control">
                            @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" wire:model="phone" class="form-control">
                            @error('phone') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select wire:model="role" class="form-select">
                                <option value="user">Khách hàng</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                            @error('role') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        @if(!$isEditMode)
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password" class="form-control">
                                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password_confirmation" class="form-control">
                                @error('password_confirmation') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                                <input type="password" wire:model="password" class="form-control">
                                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            @if($password)
                                <div class="mb-3">
                                    <label class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control">
                                    @error('password_confirmation') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            @endif
                        @endif
                        <div class="modal-footer">
                            <button type="button" wire:click="$dispatch('hide-modal')" class="btn btn-secondary">Hủy</button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="{{ $isEditMode ? 'update' : 'store' }}">
                                    {{ $isEditMode ? 'Cập nhật' : 'Thêm' }}
                                </span>
                                <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}">
                                    Đang xử lý...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-modal', () => {
                const modal = document.querySelector('.modal');
                if (modal) {
                    modal.style.display = 'block';
                    modal.classList.add('show');
                }
            });

            Livewire.on('hide-modal', () => {
                const modal = document.querySelector('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                }
            });
        });
    </script>
</div>
