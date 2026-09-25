<?php

namespace App\Livewire\Admin;

use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ReviewManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $ratingFilter = '';

    public $visibilityFilter = '';

    public function updated($property)
    {
        if (in_array($property, ['search', 'ratingFilter', 'visibilityFilter'], true)) {
            $this->resetPage();
        }
    }

    public function toggleVisibility($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_visible' => ! $review->is_visible]);
        session()->flash('message', $review->is_visible ? 'Đã hiển thị lại đánh giá.' : 'Đã ẩn đánh giá khỏi trang sách.');
    }

    public function delete($id)
    {
        Review::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa đánh giá.');
    }

    public function render()
    {
        $reviews = Review::with(['user', 'book'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('book', fn ($b) => $b->where('title', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%'))
                        ->orWhere('comment', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->ratingFilter !== '', fn ($q) => $q->where('rating', (int) $this->ratingFilter))
            ->when($this->visibilityFilter !== '', fn ($q) => $q->where('is_visible', $this->visibilityFilter === '1'))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.review-manager', [
            'reviews' => $reviews,
            'averageRating' => round((float) Review::avg('rating'), 1),
            'totalReviews' => Review::count(),
            'hiddenReviews' => Review::where('is_visible', false)->count(),
        ]);
    }
}
