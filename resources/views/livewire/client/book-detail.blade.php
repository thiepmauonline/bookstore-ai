<div>
    <!-- Breadcrumb -->
    <section class="bg-sand padding-small">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('home', ['selectedCategory' => $book->category_id]) }}#featured-books">{{ $book->category->name ?? 'Sách' }}</a></li>
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
                            <span class="me-3"><i class="bi bi-person-fill"></i> Tác giả: <strong class="text-dark">{{ $book->author->name ?? 'Đang cập nhật' }}</strong></span>
                            @endif
                            @if($book->publisher)
                            <span><i class="bi bi-building"></i> NXB: <strong class="text-dark">{{ $book->publisher->name ?? 'Đang cập nhật' }}</strong></span>
                            @endif
                        </div>

                        <div class="product-rating mb-4">
                            <div class="d-flex align-items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $averageRating)
                                        <i class="bi bi-star-fill text-warning"></i>
                                    @elseif($i - 0.5 <= $averageRating)
                                        <i class="bi bi-star-half text-warning"></i>
                                    @else
                                        <i class="bi bi-star text-warning"></i>
                                    @endif
                                @endfor
                                <span class="ms-2 fw-bold">{{ $averageRating }}/5</span>
                                <span class="ms-2 text-muted">({{ $totalReviews }} đánh giá)</span>
                            </div>
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
                            
                            <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" x-data="{ added: false, timer: null }" x-on:cart-feedback.window="if ($event.detail.added) { added = true; clearTimeout(timer); timer = setTimeout(() => added = false, 2400) }" class="btn btn-primary btn-lg px-5" {{ $book->quantity <= 0 ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="addToCart" x-text="added ? 'Đã thêm ✓' : 'Thêm vào giỏ hàng'">Thêm vào giỏ hàng</span>
                                <span wire:loading wire:target="addToCart">Đang thêm...</span>
                            </button>
                            
                            <button type="button" wire:click="toggleWishlist" class="btn btn-outline-danger btn-lg ms-2" title="{{ $isWishlisted ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' }}">
                                <i class="bi bi-heart{{ $isWishlisted ? '-fill' : '' }}"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab mô tả và đánh giá -->
            <div class="row mt-5">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fs-5 text-dark fw-bold" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Mô tả nội dung</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fs-5 text-dark fw-bold" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Đánh giá ({{ $totalReviews }})</button>
                        </li>
                    </ul>
                    <div class="tab-content py-4" id="myTabContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                            <p class="fs-6 lh-lg text-muted">
                                {{ $book->description ?? 'Chưa có mô tả cho sách này.' }}
                            </p>
                        </div>
                        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                            <!-- Form đánh giá -->
                            @if(auth()->check() && !$hasReviewed)
                                <div class="review-form bg-light p-4 rounded mb-4">
                                    <h5 class="mb-3">Viết đánh giá của bạn</h5>
                                    <form wire:submit="submitReview">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Đánh giá</label>
                                            <div class="star-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" wire:model="rating" class="d-none">
                                                    <label for="star{{ $i }}" class="star-label {{ $i <= $rating ? 'text-warning' : 'text-muted' }}">
                                                        <i class="bi bi-star{{ $i <= $rating ? '-fill' : '' }}"></i>
                                                    </label>
                                                @endfor
                                            </div>
                                            @error('rating')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nhận xét</label>
                                            <textarea wire:model="comment" class="form-control" rows="4" placeholder="Chia sẻ trải nghiệm của bạn về cuốn sách này..."></textarea>
                                            @error('comment')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <span wire:loading.remove wire:target="submitReview">Gửi đánh giá</span>
                                            <span wire:loading wire:target="submitReview">Đang gửi...</span>
                                        </button>
                                    </form>
                                </div>
                            @elseif(auth()->check() && $hasReviewed)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Bạn đã đánh giá cuốn sách này. Cảm ơn bạn!
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <a href="{{ route('login') }}" class="alert-link">Đăng nhập</a> để đánh giá sách.
                                </div>
                            @endif

                            <!-- Danh sách đánh giá -->
                            @if($reviews->count() > 0)
                                <div class="reviews-list">
                                    <h5 class="mb-4">Đánh giá từ khách hàng</h5>
                                    @foreach($reviews as $review)
                                        <div class="review-item border-bottom pb-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="fw-bold mb-1">{{ $review->user->name ?? 'Người dùng' }}</h6>
                                                    <div class="star-rating small mb-2">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                <i class="bi bi-star-fill text-warning"></i>
                                                            @else
                                                                <i class="bi bi-star text-warning"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                            </div>
                                            <p class="mb-0 text-muted">{{ $review->comment }}</p>
                                        </div>
                                    @endforeach
                                    
                                    <div class="pagination-wrapper mt-4">
                                        {{ $reviews->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <p>Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
                                </div>
                            @endif
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
