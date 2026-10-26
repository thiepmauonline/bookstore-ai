<div>
    <section class="reading-hero"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow"><span></span> SÁCH CHO HỌC TẬP VÀ CUỘC SỐNG</span><h1>Cuốn sách hay,<br><em>khởi đầu mới.</em></h1><p>Khám phá giáo trình, sách tham khảo và những câu chuyện truyền cảm hứng.</p><div class="hero-buttons"><a class="store-button" href="#featured-books">Khám phá sách <i class="bi bi-arrow-right"></i></a><a class="text-link" href="#reading-guide">Tìm sách theo nhu cầu ↗</a></div><div class="hero-footnote"><i class="bi bi-book-half"></i><span>Tri thức cho việc học. Cảm hứng cho cuộc sống.</span></div></div><div class="hero-display"><div class="hero-orbit"></div><span class="hero-caption">CẢM HỨNG TỪ NHỮNG TRANG SÁCH</span><div class="hero-books">@foreach($heroBooks as $heroBook)<a class="hero-book hero-book-{{ $loop->index }}" href="{{ route('book.detail', $heroBook->id) }}"><img src="{{ asset($heroBook->cover_image) }}" alt="{{ $heroBook->title }}" fetchpriority="high"></a>@endforeach</div><div class="reading-note"><i class="bi bi-flower1"></i><span>Dành một khoảng lặng.<br><strong>Mở một trang sách.</strong></span></div><span class="hero-edition">GÓC ĐỌC SÁCH · 01</span></div></div></section>
    <div class="store-values container"><span><i class="bi bi-journals"></i> Sách cho mọi hành trình</span><span><i class="bi bi-mortarboard"></i> Giáo trình theo ngành học</span><span><i class="bi bi-heart"></i> Lưu sách yêu thích</span></div>
    <section id="categories" class="category-section container"><div class="section-heading"><div><span class="eyebrow">KHÁM PHÁ THEO SỞ THÍCH</span><h2>Danh mục sách</h2></div><a href="#featured-books" class="text-link">Xem tất cả sách ↗</a></div><div class="category-grid">@foreach($categories as $category)<a href="{{ route('home', ['selectedCategory' => $category->id]) }}#featured-books" class="category-tile tone-{{ $loop->index % 6 }} {{ (string)$selectedCategory === (string)$category->id ? 'selected' : '' }}"><i class="bi bi-{{ ['code-slash','graph-up-arrow','heart-pulse','gear','translate','feather'][$loop->index % 6] }}"></i><strong>{{ $category->name }}</strong><span>{{ $category->books_count }} đầu sách <b>↗</b></span></a>@endforeach</div></section>
    @if($bestSellers->isNotEmpty())
    <section id="best-sellers" class="highlight-section container">
        <div class="section-heading"><div><span class="eyebrow">ĐƯỢC NHIỀU BẠN ĐỌC LỰA CHỌN</span><h2>Sách bán chạy</h2></div><a href="#featured-books" class="text-link">Xem tất cả sách ↗</a></div>
        <div class="book-grid">
            @foreach($bestSellers as $book)
                <x-book-card :book="$book" :wishlisted="isset($wishlistIds[$book->id])" :rank="$loop->iteration" wire:key="best-{{ $book->id }}">
                    <span class="book-sold"><i class="bi bi-fire"></i> Đã bán {{ number_format($book->sold_count, 0, ',', '.') }}</span>
                </x-book-card>
            @endforeach
        </div>
    </section>
    @endif
    @if($featuredBooks->isNotEmpty())
    <section id="top-rated" class="highlight-section highlight-tinted">
        <div class="container">
            <div class="section-heading"><div><span class="eyebrow">ĐƯỢC BẠN ĐỌC ĐÁNH GIÁ CAO</span><h2>Sách nổi bật</h2></div><a href="#featured-books" class="text-link">Xem tất cả sách ↗</a></div>
            <div class="book-grid">
                @foreach($featuredBooks as $book)
                    <x-book-card :book="$book" :wishlisted="isset($wishlistIds[$book->id])" wire:key="featured-{{ $book->id }}">
                        <span class="book-rating" aria-label="Đánh giá {{ number_format($book->rating_avg, 1) }} trên 5"><i class="bi bi-star-fill"></i> {{ number_format($book->rating_avg, 1, ',', '.') }} <small>({{ $book->rating_count }} đánh giá)</small></span>
                    </x-book-card>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <section id="featured-books" class="catalog-section container"><div class="section-heading"><div><span class="eyebrow">TÌM SÁCH PHÙ HỢP</span><h2>Sách và giáo trình</h2></div><span class="catalog-count">{{ $books->total() }} đầu sách</span></div>
        <div class="catalog-filters"><div class="filter-search"><label for="catalog-search">Tìm kiếm</label><input id="catalog-search" wire:model.live.debounce.300ms="search" type="search" placeholder="Nhập tên sách…"></div><div><label for="category-filter">Danh mục</label><select id="category-filter" wire:model.live="selectedCategory"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div><div><label for="major-filter">Ngành học</label><select id="major-filter" wire:model.live="selectedMajor"><option value="">Tất cả ngành học</option>@foreach($majors as $major)<option value="{{ $major->id }}">{{ $major->name }}</option>@endforeach</select></div><div><label for="course-filter">Học phần</label><select id="course-filter" wire:model.live="selectedCourse" @disabled(!$selectedMajor)><option value="">Tất cả học phần</option>@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->name }}</option>@endforeach</select></div></div>
        <div class="catalog-toolbar"><button class="text-link" wire:click="resetFilters" type="button">Đặt lại bộ lọc <i class="bi bi-arrow-counterclockwise"></i></button><div><label for="sort-books">Sắp xếp:</label><select id="sort-books" wire:model.live="sort"><option value="newest">Mới nhất</option><option value="price_asc">Giá tăng dần</option><option value="price_desc">Giá giảm dần</option></select></div></div>
        @if(session()->has('message'))<div class="alert alert-success" role="status">{{ session('message') }} <a href="{{ route('cart') }}">Xem giỏ hàng →</a></div>@endif
        <div class="book-grid" wire:loading.class="catalog-loading" wire:target="search,selectedCategory,selectedMajor,selectedCourse,sort,resetFilters">@forelse($books as $book)<x-book-card :book="$book" :wishlisted="isset($wishlistIds[$book->id])" wire:key="book-{{ $book->id }}" />@empty<div class="catalog-empty"><i class="bi bi-search"></i><h3>Không tìm thấy sách</h3><p>Thử tên sách khác hoặc điều chỉnh bộ lọc.</p><button class="store-button" wire:click="resetFilters">Xem tất cả sách</button></div>@endforelse</div><div class="catalog-pagination">{{ $books->links('components.catalog-pagination') }}</div>
    </section>
    <section id="reading-guide" class="container"><div class="reading-guide"><div><span class="eyebrow">MỖI NGÀY, MỘT CHÚT TRI THỨC</span><h2>Bạn muốn đọc gì<br>hôm nay?</h2><p>Chọn một chủ đề để tìm sách<br>phù hợp với sở thích và mục tiêu học tập.</p></div><div class="reading-topics"><a href="{{ route('home', ['search' => 'Laravel']) }}#featured-books"><span>01</span> Bắt đầu học Laravel <i class="bi bi-arrow-up-right"></i></a><a href="{{ route('home', ['search' => 'TOEIC']) }}#featured-books"><span>02</span> Luyện thi TOEIC <i class="bi bi-arrow-up-right"></i></a><a href="{{ route('home', ['search' => 'Tuổi Thơ']) }}#featured-books"><span>03</span> Sách về tuổi thơ <i class="bi bi-arrow-up-right"></i></a></div></div></section>
</div>
