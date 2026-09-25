<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-book" aria-hidden="true"></i></span>
                <div>
                    <h1 class="h3 mb-1">Quản lý Sách</h1>
                    <p class="text-muted mb-0">Thêm, sửa, xóa thông tin sách và giáo trình.</p>
                </div>
            </div>
            <div>
                <button wire:click="create" class="btn btn-primary" type="button">
                    <i class="bi bi-plus-lg"></i> Thêm sách mới
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm kiếm tên sách...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Ảnh Bìa</th>
                            <th scope="col">Tên Sách</th>
                            <th scope="col">Danh Mục</th>
                            <th scope="col">Môn Học</th>
                            <th scope="col">Giá Bán</th>
                            <th scope="col">Tồn Kho</th>
                            <th scope="col" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $book)
                            <tr>
                                <td class="ps-4">
                                    @if($book->cover_image && !str_starts_with($book->cover_image, 'assets'))
                                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="cover" class="rounded" width="48" height="64" style="object-fit: cover;">
                                    @elseif($book->cover_image)
                                        <img src="{{ asset($book->cover_image) }}" alt="cover" class="rounded" width="48" height="64" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 64px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $book->title }}</div>
                                    <div class="small text-muted">{{ $book->author->name ?? '' }}</div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $book->category->name ?? '' }}</span></td>
                                <td>{{ $book->course->name ?? 'Tham khảo chung' }}</td>
                                <td><span class="fw-semibold text-primary">{{ number_format($book->price, 0, ',', '.') }}đ</span></td>
                                <td>
                                    @if($book->quantity > 0)
                                        <span class="badge bg-success-subtle text-success">{{ $book->quantity }} cuốn</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Hết hàng</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $book->id }})" class="btn btn-sm btn-outline-primary me-1" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete({{ $book->id }})" wire:confirm="Bạn có chắc chắn muốn xóa cuốn sách này?" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Không có dữ liệu
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $books->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="bookModal" tabindex="-1" aria-labelledby="bookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookModalLabel">{{ $isEditMode ? 'Cập nhật thông tin sách' : 'Thêm sách mới' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                                <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror" required>
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mã ISBN</label>
                                <input wire:model="isbn" type="text" class="form-control @error('isbn') is-invalid @enderror">
                                @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Môn học (Học phần)</label>
                                <select wire:model="course_id" class="form-select @error('course_id') is-invalid @enderror">
                                    <option value="">-- Sách tham khảo chung (không thuộc học phần) --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                                @error('course_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                                <select wire:model="author_id" class="form-select @error('author_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn tác giả --</option>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                                    @endforeach
                                </select>
                                @error('author_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nhà xuất bản <span class="text-danger">*</span></label>
                                <select wire:model="publisher_id" class="form-select @error('publisher_id') is-invalid @enderror" required>
                                    <option value="">-- Chọn NXB --</option>
                                    @foreach($publishers as $pub)
                                        <option value="{{ $pub->id }}">{{ $pub->name }}</option>
                                    @endforeach
                                </select>
                                @error('publisher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input wire:model="price" type="number" class="form-control @error('price') is-invalid @enderror" required>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input wire:model="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Năm xuất bản</label>
                                <input wire:model="published_year" type="number" class="form-control @error('published_year') is-invalid @enderror">
                                @error('published_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Ảnh bìa</label>
                                <input wire:model="cover_image" type="file" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                                @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div wire:loading wire:target="cover_image" class="text-primary mt-2 small">Đang tải ảnh lên...</div>
                                
                                @if ($cover_image)
                                    <div class="mt-2">
                                        <img src="{{ $cover_image->temporaryUrl() }}" alt="Preview" class="img-thumbnail" width="100">
                                    </div>
                                @elseif($old_image)
                                    <div class="mt-2">
                                        @if(!str_starts_with($old_image, 'assets'))
                                            <img src="{{ asset('storage/' . $old_image) }}" alt="Current" class="img-thumbnail" width="100">
                                        @else
                                            <img src="{{ asset($old_image) }}" alt="Current" class="img-thumbnail" width="100">
                                        @endif
                                        <p class="small text-muted mt-1">Ảnh hiện tại</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Lưu thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts for Modal -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            let myModal = new bootstrap.Modal(document.getElementById('bookModal'));

            Livewire.on('show-modal', () => {
                myModal.show();
            });

            Livewire.on('hide-modal', () => {
                myModal.hide();
                // fix backdrop issue if any
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</div>
