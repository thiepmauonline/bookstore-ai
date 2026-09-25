<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Quản lý Đơn hàng</h1>
                    <p class="text-muted">Theo dõi và cập nhật trạng thái đơn hàng</p>
                </div>
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
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm mã đơn hoặc tên khách...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="statusFilter" class="form-select bg-light">
                            <option value="">-- Tất cả trạng thái --</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Mã Đơn</th>
                            <th scope="col">Khách hàng</th>
                            <th scope="col">Tổng tiền</th>
                            <th scope="col">Ngày đặt</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">{{ $order->order_code }}</td>
                                <td>
                                    <div>{{ $order->user->name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $order->user->phone ?? '' }}</div>
                                </td>
                                <td class="fw-semibold text-danger">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="viewDetails({{ $order->id }})" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Không có đơn hàng nào</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ $orders->links() }}</div>
        </div>
    </div>

    <!-- Modal Order Details -->
    <div wire:ignore.self class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @if($viewingOrder)
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết đơn hàng: <strong class="text-primary">{{ $viewingOrder->order_code }}</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    @if (session()->has('message'))
                        <div class="alert alert-success py-2">{{ session('message') }}</div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger py-2">{{ session('error') }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white fw-bold">Thông tin Khách hàng</div>
                                <div class="card-body small">
                                    <p class="mb-1"><strong>Họ tên:</strong> {{ $viewingOrder->address->receiver_name ?? $viewingOrder->user->name }}</p>
                                    <p class="mb-1"><strong>SĐT:</strong> {{ $viewingOrder->address->phone ?? $viewingOrder->user->phone }}</p>
                                    <p class="mb-1"><strong>Địa chỉ:</strong> {{ $viewingOrder->address->address ?? '' }}, {{ $viewingOrder->address->ward ?? '' }}, {{ $viewingOrder->address->district ?? '' }}, {{ $viewingOrder->address->province ?? '' }}</p>
                                    @if($viewingOrder->note)
                                        <p class="mb-0 text-danger"><strong>Ghi chú:</strong> {{ $viewingOrder->note }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white fw-bold">Thông tin Thanh toán</div>
                                <div class="card-body small">
                                    <p class="mb-1"><strong>Phương thức:</strong> {{ $viewingOrder->payment_method->label() }}</p>
                                    <p class="mb-1"><strong>Trạng thái TT:</strong>
                                        <span class="badge {{ $viewingOrder->payment_status->badgeClass() }}">{{ $viewingOrder->payment_status->label() }}</span>
                                    </p>
                                    <p class="mb-1"><strong>Tiền hàng:</strong> {{ number_format($viewingOrder->subtotal, 0, ',', '.') }}đ</p>
                                    @if($viewingOrder->discount_amount > 0)
                                        <p class="mb-1"><strong>Giảm giá{{ $viewingOrder->coupon ? ' ('.$viewingOrder->coupon->code.')' : '' }}:</strong> -{{ number_format($viewingOrder->discount_amount, 0, ',', '.') }}đ</p>
                                    @endif
                                    @if($viewingOrder->cancel_reason)
                                        <p class="mb-1 text-danger"><strong>Lý do hủy:</strong> {{ $viewingOrder->cancel_reason }}</p>
                                    @endif
                                    <p class="mb-0 fs-5 mt-2"><strong>Tổng cộng: <span class="text-danger">{{ number_format($viewingOrder->total_price, 0, ',', '.') }}đ</span></strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-white fw-bold">Sản phẩm đã đặt</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Đơn giá</th>
                                        <th>SL</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingOrder->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->book)
                                                        <img src="{{ $item->book->cover_url }}" width="40" class="me-2 rounded">
                                                        <span>{{ $item->book->title }}</span>
                                                    @else
                                                        <span class="text-muted">Sách đã bị xóa (#{{ $item->book_id }})</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td class="fw-semibold">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-wrap">
                    <div class="me-auto">
                        <strong>Trạng thái hiện tại: </strong>
                        <span class="badge {{ $viewingOrder->status->badgeClass() }} fs-6">{{ $viewingOrder->status->label() }}</span>
                    </div>

                    @php($nextStatuses = $viewingOrder->status->nextStatuses())
                    @foreach ($nextStatuses as $next)
                        @continue($next === \App\Enums\OrderStatus::Cancelled)
                        <button wire:click="updateStatus({{ $viewingOrder->id }}, '{{ $next->value }}')" wire:loading.attr="disabled" class="btn btn-primary">
                            Chuyển sang: {{ $next->label() }}
                        </button>
                    @endforeach

                    @if (in_array(\App\Enums\OrderStatus::Cancelled, $nextStatuses, true))
                        <div class="w-100 d-flex gap-2 mt-2">
                            <input wire:model="cancelReason" type="text" class="form-control @error('cancelReason') is-invalid @enderror" placeholder="Lý do hủy đơn (bắt buộc khi hủy)">
                            <button wire:click="updateStatus({{ $viewingOrder->id }}, 'cancelled')" wire:confirm="Hủy đơn này? Tồn kho và lượt mã giảm giá sẽ được hoàn lại." class="btn btn-outline-danger text-nowrap">Hủy đơn</button>
                        </div>
                        @error('cancelReason') <div class="text-danger small w-100">{{ $message }}</div> @enderror
                    @endif

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let myModal = new bootstrap.Modal(document.getElementById('orderModal'));
            Livewire.on('show-modal', () => { myModal.show(); });
        });
    </script>
</div>
