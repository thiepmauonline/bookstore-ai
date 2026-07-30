<div>
    <!-- Breadcrumb -->
    <section class="bg-sand padding-small">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="#">{{ $book->category->name ?? 'Sách' }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $book->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Chi tiết sách -->
    <section class="padding-large">
        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <div class="product-image">
                        <img src="{{ asset($book->cover_image) }}" alt="{{ $book->title }}" class="img-fluid rounded shadow-sm w-100">
                    </div>
                </div>
                <div class="col-md-7 mt-5 mt-md-0">
                    <div class="product-details">
                        <h2 class="product-title display-5 fw-semibold mb-3">{{ $book->title }}</h2>
                        
                        <div class="product-meta text-muted mb-4 fs-5">
                            @if($book->author)
                            <span class="me-3"><i class="bi bi-person-fill"></i> Tác giả: <strong class="text-dark">{{ $book->author->name }}</strong></span>
                            @endif
                            @if($book->publisher)
                            <span><i class="bi bi-building"></i> NXB: <strong class="text-dark">{{ $book->publisher->name }}</strong></span>
                            @endif
                        </div>

                        <div class="product-price fs-2 text-primary fw-bold mb-4">
                            {{ number_format($book->price, 0, ',', '.') }}đ
                        </div>

                        <div class="product-info-list mb-4">
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Mã ISBN:</strong> {{ $book->isbn ?? 'Đang cập nhật' }}</li>
                                <li class="mb-2"><strong>Danh mục:</strong> {{ $book->category->name ?? 'Đang cập nhật' }}</li>
                                @if($book->course)
                                <li class="mb-2"><strong>Học phần:</strong> {{ $book->course->name }} ({{ $book->course->major->name ?? '' }})</li>
                                @endif
                                <li class="mb-2"><strong>Trình độ:</strong> {{ $book->level ?? 'Đang cập nhật' }}</li>
                                <li class="mb-2"><strong>Năm xuất bản:</strong> {{ $book->published_year ?? 'Đang cập nhật' }}</li>
                                <li><strong>Tình trạng:</strong> 
                                    @if($book->quantity > 0)
                                        <span class="badge bg-success">Còn {{ $book->quantity }} quyển</span>
                                    @else
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @endif
                                </li>
                            </ul>
                        </div>

                        @if (session()->has('success'))
                            <div class="alert alert-success mb-4">
                                {{ session('success') }}
                                <a href="{{ route('cart') }}" class="alert-link ms-2">Xem giỏ hàng</a>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="product-action d-flex align-items-center mt-5">
                            <div class="quantity-input me-3 d-flex border rounded">
                                <button type="button" wire:click="decreaseQuantity" class="btn btn-light border-0 px-3 py-2">-</button>
                                <input type="number" wire:model.live="quantity" class="form-control border-0 text-center shadow-none" style="width: 60px;" min="1" max="{{ $book->quantity }}">
                                <button type="button" wire:click="increaseQuantity" class="btn btn-light border-0 px-3 py-2">+</button>
                            </div>
                            
                            <button type="button" wire:click="addToCart" class="btn btn-primary btn-lg px-5" {{ $book->quantity <= 0 ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="addToCart">Thêm vào giỏ hàng</span>
                                <span wire:loading wire:target="addToCart">Đang thêm...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab mô tả -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fs-5 text-dark fw-bold" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Mô tả nội dung</button>
                        </li>
                    </ul>
                    <div class="tab-content py-4" id="myTabContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                            <p class="fs-6 lh-lg text-muted">
                                {{ $book->description ?? 'Chưa có mô tả cho sách này.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sách liên quan -->
            @if($relatedBooks->count() > 0)
            <div class="row mt-5">
                <div class="col-md-12">
                    <h3 class="mb-4">Sách cùng danh mục</h3>
                    <div class="row">
                        @foreach($relatedBooks as $related)
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm h-100 product-card">
                                <a href="{{ route('book.detail', $related->id) }}">
                                    <img src="{{ asset($related->cover_image) }}" class="card-img-top p-3" alt="{{ $related->title }}">
                                </a>
                                <div class="card-body text-center">
                                    <h5 class="card-title text-truncate fs-6"><a href="{{ route('book.detail', $related->id) }}" class="text-dark text-decoration-none">{{ $related->title }}</a></h5>
                                    <p class="card-text text-primary fw-bold">{{ number_format($related->price, 0, ',', '.') }}đ</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>
