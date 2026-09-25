<div class="chatbot" x-data="{
        open: false,
        pending: '',
        scrollDown() { this.$nextTick(() => { const box = this.$refs.messages; if (box) box.scrollTop = box.scrollHeight; }) },
    }"
    x-init="$watch('open', value => value && scrollDown())"
    x-on:chatbot-answered.window="scrollDown()"
    x-on:keydown.escape.window="open = false">

    <button type="button" class="chatbot-launcher" x-on:click="open = !open" :aria-expanded="open" aria-controls="chatbot-panel" aria-label="Mở trợ lý tư vấn giáo trình">
        <i class="bi" :class="open ? 'bi-x-lg' : 'bi-chat-dots-fill'"></i>
        <span x-show="!open">Tư vấn sách</span>
    </button>

    <section id="chatbot-panel" class="chatbot-panel" x-cloak x-show="open" x-transition.opacity role="dialog" aria-label="Trợ lý tư vấn giáo trình">
        <header class="chatbot-header">
            <span class="chatbot-avatar"><i class="bi bi-robot"></i></span>
            <div class="flex-grow-1">
                <strong>Trợ lý Bookstore AI</strong>
                <small>Tư vấn giáo trình theo ngành & học phần</small>
            </div>
            <button type="button" wire:click="newConversation" class="chatbot-icon-btn" title="Cuộc trò chuyện mới" aria-label="Bắt đầu cuộc trò chuyện mới"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button type="button" x-on:click="open = false" class="chatbot-icon-btn" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
        </header>

        <div class="chatbot-messages" x-ref="messages" aria-live="polite">
            <div class="chatbot-msg bot">
                <div class="chatbot-bubble">
                    Xin chào! Mình có thể giúp bạn tìm giáo trình theo <strong>ngành học</strong> hoặc <strong>học phần</strong>. Bạn đang cần sách cho môn nào?
                </div>
            </div>

            @if ($messages->isEmpty())
                <div class="chatbot-suggestions">
                    @foreach ($suggestions as $i => $suggestion)
                        <button type="button" wire:click="askSuggestion({{ $i }})" x-on:click="pending = @js($suggestion)" wire:loading.attr="disabled">{{ $suggestion }}</button>
                    @endforeach
                </div>
            @endif

            @foreach ($messages as $message)
                <div class="chatbot-msg user" wire:key="q-{{ $message->id }}">
                    <div class="chatbot-bubble">{{ $message->question }}</div>
                </div>
                <div class="chatbot-msg bot" wire:key="a-{{ $message->id }}">
                    <div class="chatbot-bubble">
                        <div class="chatbot-answer">{!! Str::markdown($message->answer, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>

                        @foreach ($message->book_ids ?? [] as $bookId)
                            @if ($book = $books->get($bookId))
                                <a href="{{ route('book.detail', $book->id) }}" class="chatbot-book">
                                    <img src="{{ $book->cover_url }}" alt="" loading="lazy">
                                    <span>
                                        <strong>{{ $book->title }}</strong>
                                        <small>{{ number_format($book->price, 0, ',', '.') }}đ · {{ $book->quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</small>
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @endif
                        @endforeach

                        <div class="chatbot-feedback">
                            @php($helpful = $message->feedback?->is_helpful)
                            <span>Câu trả lời có hữu ích?</span>
                            <button type="button" wire:click="feedback({{ $message->id }}, true)" class="{{ $helpful === true ? 'active' : '' }}" aria-label="Hữu ích"><i class="bi bi-hand-thumbs-up"></i></button>
                            <button type="button" wire:click="feedback({{ $message->id }}, false)" class="{{ $helpful === false ? 'active' : '' }}" aria-label="Không hữu ích"><i class="bi bi-hand-thumbs-down"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Hiển thị ngay câu hỏi vừa gửi và hiệu ứng "đang trả lời" trong lúc chờ máy chủ. --}}
            <div wire:loading.block wire:target="send, askSuggestion">
                <div class="chatbot-msg user" x-show="pending"><div class="chatbot-bubble" x-text="pending"></div></div>
                <div class="chatbot-msg bot"><div class="chatbot-bubble chatbot-typing"><span></span><span></span><span></span></div></div>
            </div>
        </div>

        <form class="chatbot-form" wire:submit="send" x-on:submit="pending = $wire.question; scrollDown()">
            <input type="text" wire:model="question" maxlength="{{ \App\Services\Chatbot\ChatbotService::MAX_QUESTION_LENGTH }}" placeholder="Nhập câu hỏi, ví dụ: sách môn Kinh tế vi mô" aria-label="Câu hỏi" autocomplete="off">
            <button type="submit" wire:loading.attr="disabled" wire:target="send, askSuggestion" aria-label="Gửi"><i class="bi bi-send-fill"></i></button>
        </form>
        @error('question') <div class="chatbot-error">{{ $message }}</div> @enderror
    </section>
</div>
