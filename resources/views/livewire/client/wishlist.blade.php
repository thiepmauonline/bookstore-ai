<div>
    @section('title', 'Danh sách yêu thích')
    
    <section class="padding-large">
        <div class="container">
            <h2 class="display-6 mb-4">Danh sách yêu thích</h2>
            
            @if(session('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($wishlistItems->count() > 0)
                <div class="row">
                    @foreach($wishlistItems as $item)
                        @if($item->book)
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="product-card h-100">
                                    <div class="product-img">
                                        <img src="{{ $item->book->cover_url }}" alt="{{ $item->book->title }}">
                                        <div class="product-action">
                                            <button wire:click="addToCart({{ $item->book->id }})" class="btn btn-primary btn-sm" title="Thêm vào giỏ hàng">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                            <button wire:click="removeFromWishlist({{ $item->book->id }})" class="btn btn-danger btn-sm" title="Xóa khỏi yêu thích">
                                                <i class="bi bi-heart-fill"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="product-content">
                                        <h3 class="product-title">
                                            <a href="{{ route('book.detail', $item->book->id) }}">
                                                {{ Str::limit($item->book->title, 50) }}
                                            </a>
                                        </h3>
                                        <div class="product-meta">
                                            @if($item->book->author)
                                                <span>{{ $item->book->author->name ?? 'Đang cập nhật' }}</span>
                                            @endif
                                        </div>
                                        <div class="product-price">
                                            {{ number_format($item->book->price, 0, ',', '.') }}₫
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                <div class="pagination-wrapper mt-4">
                    {{ $wishlistItems->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-heart display-1 text-muted"></i>
                    <h4 class="mt-3">Danh sách yêu thích trống</h4>
                    <p class="text-muted">Bạn chưa thêm sách nào vào danh sách yêu thích.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">Khám phá sách ngay</a>
                </div>
            @endif
        </div>
    </section>
</div>
