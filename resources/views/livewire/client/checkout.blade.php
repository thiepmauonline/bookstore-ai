<div>
    <section class="padding-large">
        <div class="container">
            <h2 class="display-5 mb-5 text-center">Thanh toán</h2>
            
            @if (session()->has('checkout_error'))
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('checkout_error') }}
                </div>
            @endif

            <div class="row">
                <!-- Thông tin giao hàng -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Thông tin giao hàng</h4>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label">Người nhận <span class="text-danger">*</span></label>
                                    <input wire:model="receiver_name" type="text" class="form-control @error('receiver_name') is-invalid @enderror">
                                    @error('receiver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-sm-4">
                                    <label class="form-label">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                                    <input wire:model="province" type="text" class="form-control @error('province') is-invalid @enderror" placeholder="VD: Hà Nội">
                                    @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Quận / Huyện <span class="text-danger">*</span></label>
                                    <input wire:model="district" type="text" class="form-control @error('district') is-invalid @enderror">
                                    @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">Phường / Xã <span class="text-danger">*</span></label>
                                    <input wire:model="ward" type="text" class="form-control @error('ward') is-invalid @enderror">
                                    @error('ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                    <input wire:model="address" type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Số nhà, tên đường...">
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Ghi chú thêm</label>
                                    <textarea wire:model="note" class="form-control" rows="3" placeholder="Ghi chú về giao hàng, đóng gói..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Đơn hàng & Thanh toán -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Tóm tắt đơn hàng</h4>
                            
                            <ul class="list-group mb-3 border-0">
                                @foreach($cartItems as $item)
                                    <li class="list-group-item d-flex justify-content-between lh-sm bg-transparent border-bottom">
                                        <div>
                                            <h6 class="my-0">{{ $item['title'] }}</h6>
                                            <small class="text-muted">Số lượng: {{ $item['quantity'] }}</small>
                                        </div>
                                        <span class="text-muted">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính</span>
                                <strong>{{ number_format($subtotal, 0, ',', '.') }} VNĐ</strong>
                            </div>

                            @if($appliedCoupon)
                                <div class="d-flex justify-content-between text-success mb-2">
                                    <span>
                                        Khuyến mãi ({{ $appliedCoupon->code }})
                                        <button wire:click="removeCoupon" class="btn btn-link btn-sm text-danger p-0 ms-1 text-decoration-none">Bỏ</button>
                                    </span>
                                    <strong>-{{ number_format($discountAmount, 0, ',', '.') }} VNĐ</strong>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mb-4 border-top pt-3">
                                <span class="fs-5">Tổng tiền</span>
                                <strong class="fs-4 text-primary">{{ number_format($total, 0, ',', '.') }} VNĐ</strong>
                            </div>

                            <!-- Mã giảm giá -->
                            <div class="input-group mb-4">
                                <input wire:model="couponCode" type="text" class="form-control @error('couponCode') is-invalid @enderror" placeholder="Nhập mã giảm giá" @if($appliedCoupon) disabled @endif>
                                <button wire:click="applyCoupon" type="button" class="btn btn-secondary" @if($appliedCoupon) disabled @endif>
                                    <span wire:loading.remove wire:target="applyCoupon">Áp dụng</span>
                                    <span wire:loading wire:target="applyCoupon">Đang xử lý...</span>
                                </button>
                                @error('couponCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if (session()->has('coupon_message'))
                                <div class="text-success small mb-3">{{ session('coupon_message') }}</div>
                            @endif

                            <hr class="my-4">

                            <h5 class="mb-3">Phương thức thanh toán</h5>
                            <div class="d-flex align-items-start gap-2 my-3 p-3 bg-light rounded">
                                <i class="bi bi-cash-coin fs-4 text-success"></i>
                                <div>
                                    <div class="fw-semibold">Thanh toán khi nhận hàng (COD)</div>
                                    <div class="small text-muted">Bạn thanh toán bằng tiền mặt cho nhân viên giao hàng khi nhận sách.</div>
                                </div>
                            </div>

                            <button wire:click="placeOrder" wire:loading.attr="disabled" class="btn btn-primary btn-lg w-100 rounded-pill mt-3" type="button">
                                <span wire:loading.remove wire:target="placeOrder">Xác nhận Đặt hàng</span>
                                <span wire:loading wire:target="placeOrder" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
