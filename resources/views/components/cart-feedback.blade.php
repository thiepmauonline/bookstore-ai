<div x-data="{ open: false, added: true, timer: null, dismiss() { this.open = false; clearTimeout(this.timer) } }"
     x-on:cart-feedback.window="added = $event.detail.added; open = true; clearTimeout(timer); timer = setTimeout(() => open = false, 5000)"
     x-on:keydown.escape.window="dismiss()"
     class="cart-feedback-container">
    <div x-cloak x-show="open" x-transition:enter="feedback-enter" x-transition:enter-start="feedback-hidden" x-transition:enter-end="feedback-visible" x-transition:leave="feedback-leave" x-transition:leave-start="feedback-visible" x-transition:leave-end="feedback-hidden"
         class="cart-feedback" :class="{ 'cart-feedback-warning': !added }" role="status" aria-live="polite" aria-atomic="true"
         @mouseenter="clearTimeout(timer)" @mouseleave="timer = setTimeout(() => open = false, 5000)" @focusin="clearTimeout(timer)" @focusout="timer = setTimeout(() => open = false, 5000)">
        <i class="bi feedback-icon" :class="added ? 'bi-bag-check' : 'bi-info-circle'"></i>
        <div><strong x-text="added ? 'Đã thêm sách vào giỏ!' : 'Đã đạt số lượng tối đa'"></strong><p x-text="added ? 'Cuốn sách bạn chọn đang chờ trong giỏ hàng.' : 'Giỏ hàng đã có đủ số lượng sách còn trong kho.'"></p><a href="{{ route('cart') }}">Xem giỏ hàng <i class="bi bi-arrow-right"></i></a></div>
        <button type="button" @click="dismiss()" aria-label="Đóng thông báo"><i class="bi bi-x-lg"></i></button>
    </div>
</div>
