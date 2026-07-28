<div>
    <section class="padding-large">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="display-5 mb-4">Giỏ hàng của bạn</h2>
                    
                    @if(count($cartItems) > 0)
                        <div class="table-responsive mb-5">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 15%">Sản phẩm</th>
                                        <th scope="col" style="width: 35%">Tên sách</th>
                                        <th scope="col" style="width: 15%">Đơn giá</th>
                                        <th scope="col" style="width: 20%">Số lượng</th>
                                        <th scope="col" style="width: 15%">Thành tiền</th>
                                        <th scope="col">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                        <tr>
                                            <td>
                                                <img src="{{ asset($item['cover_image'] ?? 'assets/client/images/product-item1.jpg') }}" alt="{{ $item['title'] }}" class="img-fluid rounded" style="max-height: 100px;">
                                            </td>
                                            <td class="fw-bold">{{ $item['title'] }}</td>
                                            <td>{{ number_format($item['price'], 0, ',', '.') }} VNĐ</td>
                                            <td>
                                                <div class="input-group" style="width: 120px;">
                                                    <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="btn btn-outline-secondary" type="button" @if($item['quantity'] <= 1) disabled @endif>-</button>
                                                    <input type="text" class="form-control text-center" value="{{ $item['quantity'] }}" readonly>
                                                    <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="btn btn-outline-secondary" type="button" @if($item['quantity'] >= $item['max_quantity']) disabled @endif>+</button>
                                                </div>
                                                @if($item['quantity'] >= $item['max_quantity'])
                                                    <small class="text-danger mt-1 d-block">Tối đa {{ $item['max_quantity'] }} cuốn</small>
                                                @endif
                                            </td>
                                            <td class="text-primary fw-bold">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} VNĐ</td>
                                            <td>
                                                <button wire:click="removeItem({{ $item['id'] }})" class="btn btn-danger btn-sm">
                                                    <i class="icon icon-close"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="card bg-light border-0">
                                    <div class="card-body p-4">
                                        <h3 class="card-title mb-4">Tổng cộng giỏ hàng</h3>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fs-5">Tổng tiền:</span>
                                            <span class="fs-4 text-primary fw-bold">{{ number_format($total, 0, ',', '.') }} VNĐ</span>
                                        </div>
                                        <hr>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg rounded-pill">Tiến hành Thanh toán</a>
                                            <a href="{{ route('home') }}" class="btn btn-outline-dark btn-lg rounded-pill mt-2">Tiếp tục mua hàng</a>
                                            <button wire:click="clearCart" wire:confirm="Xóa toàn bộ giỏ hàng?" class="btn btn-link text-danger mt-2">Xóa toàn bộ giỏ hàng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="icon icon-shopping-cart display-1 text-muted mb-3 d-block"></i>
                            <h3 class="text-muted">Giỏ hàng của bạn đang trống!</h3>
                            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill mt-4">Quay lại mua sắm</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
