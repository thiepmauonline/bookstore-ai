@props(['book', 'wishlisted' => false, 'rank' => null])

{{-- Thẻ sách dùng chung cho trang chủ; $slot là dòng thông tin phụ (đã bán, đánh giá). --}}
<article {{ $attributes->merge(['class' => 'book-card']) }}>
    <div class="book-art">
        @if($rank)<span class="book-rank">#{{ $rank }}</span>@endif
        <a href="{{ route('book.detail', $book->id) }}"><img loading="lazy" src="{{ $book->cover_url }}" alt="{{ $book->title }}" width="1024" height="1536"></a>
        <button class="book-wishlist {{ $wishlisted ? 'saved' : '' }}" wire:click="toggleWishlist({{ $book->id }})" aria-label="{{ $wishlisted ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' }}" aria-pressed="{{ $wishlisted ? 'true' : 'false' }}"><i class="bi bi-heart{{ $wishlisted ? '-fill' : '' }}"></i></button>
    </div>
    <div class="book-info">
        <span class="book-category">{{ $book->category->name ?? 'Sách tham khảo' }}</span>
        <h3><a href="{{ route('book.detail', $book->id) }}">{{ $book->title }}</a></h3>
        <p>{{ $book->author->name ?? 'Tác giả đang cập nhật' }}</p>
        @if($slot->isNotEmpty())<div class="book-meta">{{ $slot }}</div>@endif
        <div class="book-purchase">
            <strong>{{ number_format($book->price, 0, ',', '.') }}<small>₫</small></strong>
            <button x-data="{ added: false, timer: null }" x-on:cart-feedback.window="if ($event.detail.bookId == {{ $book->id }} &amp;&amp; $event.detail.added) { added = true; clearTimeout(timer); timer = setTimeout(() => added = false, 2400) }" :class="{ 'just-added': added }" wire:click="addToCart({{ $book->id }})" wire:loading.attr="disabled" wire:target="addToCart({{ $book->id }})" @disabled($book->quantity <= 0) aria-label="Thêm {{ $book->title }} vào giỏ hàng"><span class="cart-button-content" wire:loading.remove wire:target="addToCart({{ $book->id }})"><i class="bi" :class="added ? 'bi-check2' : 'bi-bag-plus'"></i><span x-text="added ? 'Đã thêm' : '{{ $book->quantity > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}'">{{ $book->quantity > 0 ? 'Thêm vào giỏ' : 'Hết hàng' }}</span></span><span class="cart-button-content" wire:loading.inline-flex wire:target="addToCart({{ $book->id }})"><i class="cart-spinner"></i><span>Đang thêm…</span></span></button>
        </div>
    </div>
</article>
