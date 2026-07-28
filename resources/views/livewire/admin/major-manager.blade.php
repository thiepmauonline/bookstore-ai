<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Quản lý Ngành Học & Học Phần</h1>
                </div>
            </div>
            <div>
                <button wire:click="create" class="btn btn-primary" type="button">
                    <i class="bi bi-plus-lg"></i> Thêm ngành học
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
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm kiếm ngành học...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">ID</th>
                            <th scope="col">Tên ngành học</th>
                            <th scope="col">Số lượng học phần</th>
                            <th scope="col" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($majors as $item)
                            <tr>
                                <td class="ps-4">{{ $item->id }}</td>
                                <td class="fw-bold">{{ $item->name }}</td>
                                <td><span class="badge bg-info-subtle text-info">{{ $item->courses_count }} môn</span></td>
                                <td class="text-end pe-4">
                                    <button wire:click="manageCourses({{ $item->id }})" class="btn btn-sm btn-outline-info me-1" title="Quản lý Học Phần">
                                        <i class="bi bi-journal-bookmark-fill"></i> Học phần
                                    </button>
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Bạn có chắc chắn muốn xóa ngành này?" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $majors->links() }}</div>
        </div>
    </div>

    <!-- Modal Major -->
    <div wire:ignore.self class="modal fade" id="majorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Cập nhật Ngành học' : 'Thêm Ngành học' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên ngành học <span class="text-danger">*</span></label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Courses -->
    <div wire:ignore.self class="modal fade" id="coursesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Quản lý Học Phần - <strong>{{ $managingCoursesFor?->name }}</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    @if (session()->has('course_message'))
                        <div class="alert alert-success py-2">{{ session('course_message') }}</div>
                    @endif
                    @if (session()->has('course_error'))
                        <div class="alert alert-danger py-2">{{ session('course_error') }}</div>
                    @endif

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <form wire:submit="saveCourse" class="d-flex align-items-center gap-2">
                                <input wire:model="course_name" type="text" class="form-control @error('course_name') is-invalid @enderror" placeholder="Tên học phần mới...">
                                <button type="submit" class="btn btn-primary text-nowrap">
                                    {{ $isCourseEditMode ? 'Cập nhật' : 'Thêm Môn' }}
                                </button>
                                @if($isCourseEditMode)
                                    <button type="button" wire:click="resetCourseForm" class="btn btn-secondary">Hủy</button>
                                @endif
                            </form>
                            @error('course_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <ul class="list-group list-group-flush">
                            @if($managingCoursesFor)
                                @forelse($managingCoursesFor->courses as $course)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $course->name }}</span>
                                        <div>
                                            <button wire:click="editCourse({{ $course->id }})" class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil"></i> Sửa</button>
                                            <button wire:click="deleteCourse({{ $course->id }})" wire:confirm="Bạn muốn xóa học phần này?" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i> Xóa</button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">Chưa có học phần nào.</li>
                                @endforelse
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let majorModal = new bootstrap.Modal(document.getElementById('majorModal'));
            let coursesModal = new bootstrap.Modal(document.getElementById('coursesModal'));
            
            Livewire.on('show-major-modal', () => majorModal.show());
            Livewire.on('hide-major-modal', () => { 
                majorModal.hide(); 
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });

            Livewire.on('show-courses-modal', () => coursesModal.show());
        });
    </script>
</div>
