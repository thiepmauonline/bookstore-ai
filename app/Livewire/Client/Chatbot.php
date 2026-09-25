<?php

namespace App\Livewire\Client;

use App\Models\Book;
use App\Models\ChatbotHistory;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Khung chat tư vấn giáo trình, gắn vào layout phía khách hàng.
 */
class Chatbot extends Component
{
    /** Số câu hỏi tối đa trong một phút cho mỗi phiên, tránh lạm dụng API. */
    private const MAX_PER_MINUTE = 10;

    /** Số lượt hỏi đáp hiển thị lại khi mở khung chat. */
    private const VISIBLE_MESSAGES = 20;

    private const SESSION_KEY = 'chatbot.session_id';

    public const SUGGESTIONS = [
        'Sách cho môn Lập trình Web',
        'Giáo trình Kinh tế vĩ mô',
        'Mình học ngành Y, nên mua sách gì?',
        'Sách luyện thi TOEIC',
    ];

    public string $question = '';

    public function send(ChatbotService $chatbot): void
    {
        $this->validate(
            ['question' => 'required|string|max:'.ChatbotService::MAX_QUESTION_LENGTH],
            ['question.required' => 'Bạn hãy nhập câu hỏi nhé.', 'question.max' => 'Câu hỏi quá dài.']
        );

        $key = 'chatbot:'.$this->sessionId();
        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_MINUTE)) {
            $this->addError('question', 'Bạn hỏi hơi nhanh, vui lòng chờ '.RateLimiter::availableIn($key).' giây.');

            return;
        }
        RateLimiter::hit($key, 60);

        $chatbot->ask($this->question, $this->sessionId(), auth()->user());

        $this->reset('question');
        $this->dispatch('chatbot-answered');
    }

    public function askSuggestion(int $index): void
    {
        $this->question = self::SUGGESTIONS[$index] ?? '';
        $this->send(app(ChatbotService::class));
    }

    public function feedback(int $historyId, bool $isHelpful): void
    {
        // Chỉ cho đánh giá câu trả lời thuộc phiên chat của chính mình.
        $history = ChatbotHistory::where('session_id', $this->sessionId())->findOrFail($historyId);
        app(ChatbotService::class)->recordFeedback($history, $isHelpful);
    }

    public function newConversation(): void
    {
        session()->put(self::SESSION_KEY, (string) Str::uuid());
        $this->resetErrorBag();
    }

    private function sessionId(): string
    {
        if (! session()->has(self::SESSION_KEY)) {
            session()->put(self::SESSION_KEY, (string) Str::uuid());
        }

        return session(self::SESSION_KEY);
    }

    public function render()
    {
        $messages = ChatbotHistory::with('feedback')
            ->where('session_id', $this->sessionId())
            ->latest('id')
            ->take(self::VISIBLE_MESSAGES)
            ->get()
            ->reverse()
            ->values();

        $books = Book::whereIn('id', $messages->pluck('book_ids')->flatten()->filter()->unique())
            ->get()
            ->keyBy('id');

        return view('livewire.client.chatbot', [
            'messages' => $messages,
            'books' => $books,
            'suggestions' => self::SUGGESTIONS,
        ]);
    }
}
