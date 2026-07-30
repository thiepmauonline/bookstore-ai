<div>
    <!-- Breadcrumb -->
    <section class="bg-sand padding-small">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Đơn hàng của tôi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Nội dung đơn hàng -->
    <section class="padding-large">
        <div class="container">
            <h2 class="mb-4">Lịch sử đơn hàng</h2>
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-4">Mã ĐH</th>
                                    <th class="py-3">Ngày đặt</th>
                                    <th class="py-3">Tổng tiền</th>
                                    <th class="py-3">Thanh toán</th>
                                    <th class="py-3">Trạng thái</th>
                                    <th class="py-3 text-end px-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td class="fw-bold px-4">#MĐH-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-primary fw-bold">{{ number_format($order->total_price, 0, ',', '.') }}đ</td>
                                        <td>
                                            @if($order->payment_method == 'cod')
                                                <span class="badge bg-secondary">Thanh toán khi nhận hàng</span>
                                            @else
                                                <span class="badge bg-info">Chuyển khoản / Online</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="badge bg-warning text-dark">Đang chờ duyệt</span>
                                                    @break
                                                @case('processing')
                                                    <span class="badge bg-primary">Đang giao hàng</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Đã hoàn thành</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge bg-danger">Đã hủy</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="text-end px-4">
                                            <button wire:click="viewOrder({{ $order->id }})" class="btn btn-sm btn-outline-primary">Xem chi tiết</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            Bạn chưa có đơn hàng nào.<br>
                                            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </section>

    <!-- Modal Chi Tiết Đơn Hàng -->
    <div wire:ignore.self class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @if($selectedOrder)
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Chi tiết đơn hàng #MĐH-{{ str_pad($selectedOrder->id, 5, '0', STR_PAD_LEFT) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Thông tin giao hàng</h6>
                            <p class="mb-1"><strong>Người nhận:</strong> {{ $selectedOrder->address->receiver_name ?? $selectedOrder->user->name ?? 'Khách hàng' }}</p>
                            <p class="mb-1"><strong>Điện thoại:</strong> {{ $selectedOrder->address->phone ?? $selectedOrder->user->phone ?? 'Không có' }}</p>
                            <p class="mb-0"><strong>Địa chỉ:</strong> {{ $selectedOrder->address->address ?? 'Không có' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted mb-2">Thông tin thanh toán</h6>
                            <p class="mb-1"><strong>Tổng tiền hàng:</strong> {{ number_format($selectedOrder->total_price, 0, ',', '.') }}đ</p>
                            <p class="mb-1"><strong>Phí vận chuyển:</strong> 0đ</p>
                            <h5 class="text-primary mt-2"><strong>Tổng thanh toán: {{ number_format($selectedOrder->total_price, 0, ',', '.') }}đ</strong></h5>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3">Sản phẩm đã đặt</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedOrder->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset($item->book->cover_image ?? 'assets/client/images/product-item1.jpg') }}" alt="{{ $item->book->title ?? 'Sách' }}" style="width: 50px; height: 70px; object-fit: cover;" class="me-3 rounded shadow-sm">
                                            <span>{{ $item->book->title ?? 'Sách đã xóa' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                                    <td class="text-center align-middle">{{ $item->quantity }}</td>
                                    <td class="text-end align-middle fw-bold">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-order-modal', () => {
                let modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
                modal.show();
            });
        });
    </script>
</div>
