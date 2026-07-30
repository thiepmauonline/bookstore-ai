<div>
    <section id="billboard">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <button class="prev slick-arrow">
                        <i class="icon icon-arrow-left"></i>
                    </button>

                    <div class="main-slider pattern-overlay">
                        <div class="slider-item">
                            <div class="banner-content">
                                <h2 class="banner-title">Khám phá tri thức</h2>
                                <p>Bookstore-AI mang đến hàng ngàn tựa sách và giáo trình phục vụ cho mọi ngành học, với sự tư vấn thông minh từ AI.</p>
                                <div class="btn-wrap">
                                    <a href="#featured-books" class="btn btn-outline-accent btn-accent-arrow">Đọc thêm<i class="icon icon-ns-arrow-right"></i></a>
                                </div>
                            </div>
                            <img src="{{ asset('assets/client/images/main-banner1.jpg') }}" alt="banner" class="banner-image">
                        </div>
                    </div>

                    <button class="next slick-arrow">
                        <i class="icon icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section id="featured-books" class="py-5 my-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-header align-center">
                        <div class="title">
                            <span>Sản phẩm chất lượng</span>
                        </div>
                        <h2 class="section-title">Sách & Giáo Trình</h2>
                    </div>

                    <!-- Lọc sách -->
                    <div class="filter-section bg-light p-4 rounded mb-5">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tìm kiếm sách</label>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Nhập tên sách...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ngành học</label>
                                <select wire:model.live="selectedMajor" class="form-select">
                                    <option value="">-- Tất cả ngành học --</option>
                                    @foreach($majors as $major)
                                        <option value="{{ $major->id }}">{{ $major->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Học phần</label>
                                <select wire:model.live="selectedCourse" class="form-select" @if(!$selectedMajor) disabled @endif>
                                    <option value="">-- Chọn học phần --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Kết thúc Lọc sách -->

                    <div class="product-list" data-aos="fade-up">
                        <div class="row">
                            @forelse($books as $book)
                                <div class="col-md-3 mb-4">
                                    <div class="product-item">
                                        <figure class="product-style">
                                            <img src="{{ asset($book->cover_image ?? 'assets/client/images/product-item1.jpg') }}" alt="{{ $book->title }}" class="product-item" style="object-fit: cover; height: 350px;">
                                            <button type="button" wire:click="addToCart({{ $book->id }})" class="add-to-cart" data-product-tile="add-to-cart">
                                                <span wire:loading.remove wire:target="addToCart({{ $book->id }})">Thêm vào giỏ</span>
                                                <span wire:loading wire:target="addToCart({{ $book->id }})">Đang thêm...</span>
                                            </button>
                                        </figure>
                                        <figcaption>
                                            <h3><a href="{{ route('book.detail', $book->id) }}">{{ $book->title }}</a></h3>
                                            <span>{{ $book->author->name ?? 'Đang cập nhật' }}</span>
                                            <div class="item-price">{{ number_format($book->price, 0, ',', '.') }} VNĐ</div>
                                        </figcaption>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <p class="text-muted">Không tìm thấy sách nào phù hợp với bộ lọc.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Phân trang -->
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $books->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
