<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Quản lý Đánh giá</h1>
                    <p class="text-muted mb-0">
                        {{ $totalReviews }} đánh giá · trung bình <strong>{{ $averageRating }}</strong> <i class="bi bi-star-fill text-warning"></i>
                        · {{ $hiddenReviews }} đang ẩn
                    </p>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm theo tên sách, khách hàng, nội dung...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="ratingFilter" class="form-select bg-light">
                            <option value="">-- Tất cả số sao --</option>
                            @for ($star = 5; $star >= 1; $star--)
                                <option value="{{ $star }}">{{ $star }} sao</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="visibilityFilter" class="form-select bg-light">
                            <option value="">-- Hiển thị & ẩn --</option>
                            <option value="1">Đang hiển thị</option>
                            <option value="0">Đang ẩn</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Sách</th>
                            <th>Khách hàng</th>
                            <th>Đánh giá</th>
                            <th style="width: 35%">Nội dung</th>
                            <th>Ngày</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            <tr class="{{ $review->is_visible ? '' : 'table-secondary' }}">
                                <td class="ps-4">
                                    @if ($review->book)
                                        <a href="{{ route('book.detail', $review->book_id) }}" target="_blank" class="fw-semibold text-decoration-none">{{ $review->book->title }}</a>
                                    @else
                                        <span class="text-muted">Sách đã xóa</span>
                                    @endif
                                </td>
                                <td>{{ $review->user->name ?? 'N/A' }}</td>
                                <td class="text-warning text-nowrap">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </td>
                                <td class="small">{{ $review->comment }}</td>
                                <td class="small text-nowrap">{{ $review->created_at->format('d/m/Y') }}</td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button wire:click="toggleVisibility({{ $review->id }})" class="btn btn-sm btn-outline-secondary me-1" title="{{ $review->is_visible ? 'Ẩn' : 'Hiện' }}">
                                        <i class="bi {{ $review->is_visible ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                    </button>
                                    <button wire:click="delete({{ $review->id }})" wire:confirm="Xóa vĩnh viễn đánh giá này?" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Không có đánh giá nào</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $reviews->links() }}</div>
        </div>
    </div>
</div>
