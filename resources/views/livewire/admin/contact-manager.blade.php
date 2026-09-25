<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Liên hệ từ khách hàng</h1>
                    <p class="text-muted mb-0">{{ $unhandledCount }} liên hệ chưa xử lý</p>
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
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm theo tên, email, nội dung...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="statusFilter" class="form-select bg-light">
                            <option value="">-- Tất cả --</option>
                            <option value="0">Chưa xử lý</option>
                            <option value="1">Đã xử lý</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Người gửi</th>
                            <th style="width: 45%">Nội dung</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr wire:key="contact-{{ $contact->id }}">
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $contact->name }}</div>
                                    <a href="mailto:{{ $contact->email }}" class="small">{{ $contact->email }}</a>
                                </td>
                                <td class="small" style="white-space: pre-line">{{ $contact->message }}</td>
                                <td class="small text-nowrap">{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($contact->is_handled)
                                        <span class="badge bg-success">Đã xử lý</span>
                                        <div class="small text-muted">{{ $contact->handled_at?->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="badge bg-warning text-dark">Chưa xử lý</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <button wire:click="toggleHandled({{ $contact->id }})" class="btn btn-sm {{ $contact->is_handled ? 'btn-outline-secondary' : 'btn-outline-success' }} me-1" title="{{ $contact->is_handled ? 'Đánh dấu chưa xử lý' : 'Đánh dấu đã xử lý' }}">
                                        <i class="bi {{ $contact->is_handled ? 'bi-arrow-counterclockwise' : 'bi-check2' }}"></i>
                                    </button>
                                    <button wire:click="delete({{ $contact->id }})" wire:confirm="Xóa liên hệ này?" class="btn btn-sm btn-outline-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có liên hệ nào</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $contacts->links() }}</div>
        </div>
    </div>
</div>
