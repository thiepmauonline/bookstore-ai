<?php

namespace App\Livewire\Admin;

use App\Models\Book;
use App\Models\ChatbotFeedback;
use App\Models\ChatbotHistory;
use App\Services\Chatbot\GeminiClient;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Theo dõi dữ liệu chatbot: lịch sử hỏi đáp, phản hồi của khách, nguồn trả lời.
 */
#[Layout('components.layouts.admin')]
class ChatbotManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $feedbackFilter = '';

    public $sourceFilter = '';

    public $viewing = null;

    public function updated($property)
    {
        if (in_array($property, ['search', 'feedbackFilter', 'sourceFilter'], true)) {
            $this->resetPage();
        }
    }

    public function showDetails($id)
    {
        $this->viewing = ChatbotHistory::with(['user', 'feedback'])->findOrFail($id);
        $this->dispatch('show-modal');
    }

    public function delete($id)
    {
        $history = ChatbotHistory::findOrFail($id);
        $history->feedbacks()->delete();
        $history->delete();
        session()->flash('message', 'Đã xóa lượt hỏi đáp.');
    }

    public function render()
    {
        $histories = ChatbotHistory::with(['user', 'feedback'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('question', 'like', '%'.$this->search.'%')
                        ->orWhere('answer', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->feedbackFilter === 'helpful', fn ($q) => $q->whereHas('feedback', fn ($f) => $f->where('is_helpful', true)))
            ->when($this->feedbackFilter === 'unhelpful', fn ($q) => $q->whereHas('feedback', fn ($f) => $f->where('is_helpful', false)))
            ->when($this->feedbackFilter === 'none', fn ($q) => $q->doesntHave('feedback'))
            ->when(in_array($this->sourceFilter, [ChatbotHistory::SOURCE_AI, ChatbotHistory::SOURCE_FALLBACK], true), fn ($q) => $q->where('source', $this->sourceFilter))
            ->latest()
            ->paginate(15);

        $feedbackTotal = ChatbotFeedback::count();
        $helpful = ChatbotFeedback::where('is_helpful', true)->count();

        // Sách được chatbot gợi ý nhiều nhất.
        $topBookIds = ChatbotHistory::whereNotNull('book_ids')->pluck('book_ids')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(5);
        $topBooks = Book::whereIn('id', $topBookIds->keys())->pluck('title', 'id');

        return view('livewire.admin.chatbot-manager', [
            'histories' => $histories,
            'stats' => [
                'questions' => ChatbotHistory::count(),
                'sessions' => ChatbotHistory::distinct('session_id')->count('session_id'),
                'helpfulRate' => $feedbackTotal ? round($helpful * 100 / $feedbackTotal) : null,
                'feedbackTotal' => $feedbackTotal,
                'fallback' => ChatbotHistory::where('source', ChatbotHistory::SOURCE_FALLBACK)->count(),
                'avgMs' => (int) ChatbotHistory::where('source', ChatbotHistory::SOURCE_AI)->avg('response_ms'),
            ],
            'topBooks' => $topBookIds->mapWithKeys(fn ($count, $id) => [$topBooks[$id] ?? "Sách #{$id}" => $count]),
            'aiConfigured' => app(GeminiClient::class)->isConfigured(),
            'aiModel' => config('services.gemini.model'),
        ]);
    }
}
