<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Quản lý mã giảm giá</h1>
                </div>
            </div>
            <div>
                <button wire:click="create" class="btn btn-primary" type="button">
                    <i class="bi bi-plus-lg"></i> Thêm mã giảm giá
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm theo mã giảm giá...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">STT</th>
                            <th scope="col">Mã giảm giá</th>
                            <th scope="col">Mức giảm</th>
                            <th scope="col">Lượt còn lại</th>
                            <th scope="col">Thời gian áp dụng</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($coupons as $item)
                            <tr>
                                <td class="ps-4">{{ $coupons->firstItem() + $loop->index }}</td>
                                <td class="fw-bold text-uppercase">{{ $item->code }}</td>
                                <td class="text-primary fw-semibold">{{ number_format($item->discount, 0, ',', '.') }} VNĐ</td>
                                <td>{{ $item->quantity }} lượt</td>
                                <td>
                                    <div class="small">Từ: {{ $item->start_date->format('d/m/Y H:i') }}</div>
                                    <div class="small text-danger">Đến: {{ $item->end_date->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    @if(now()->between($item->start_date, $item->end_date) && $item->quantity > 0)
                                        <span class="badge bg-success">Có hiệu lực</span>
                                    @elseif(now()->lt($item->start_date))
                                        <span class="badge bg-warning text-dark">Chưa có hiệu lực</span>
                                    @else
                                        <span class="badge bg-secondary">Hết hạn hoặc hết lượt</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap pe-4">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Xóa mã giảm giá này?" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Không tìm thấy mã giảm giá.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $coupons->links() }}</div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self x-data="adminModal('show-modal', 'hide-modal')" class="modal" id="crudModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Cập nhật' : 'Thêm' }} mã giảm giá</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Mã giảm giá (tự động viết hoa) <span class="text-danger">*</span></label>
                                <input wire:model="code" type="text" class="form-control text-uppercase @error('code') is-invalid @enderror">
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lượt còn lại <span class="text-danger">*</span></label>
                                <input wire:model="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror">
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Mức giảm (VNĐ) <span class="text-danger">*</span></label>
                                <input wire:model="discount" type="number" class="form-control @error('discount') is-invalid @enderror">
                                @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input wire:model="start_date" type="datetime-local" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input wire:model="end_date" type="datetime-local" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}" class="spinner-border spinner-border-sm me-2"></span>
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
