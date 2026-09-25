<?php

namespace App\Livewire\Admin;

use App\Models\Contact;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ContactManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $statusFilter = '';

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function toggleHandled($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update([
            'is_handled' => ! $contact->is_handled,
            'handled_at' => $contact->is_handled ? null : now(),
        ]);
        session()->flash('message', $contact->is_handled ? 'Đã đánh dấu đã xử lý.' : 'Đã chuyển về chưa xử lý.');
    }

    public function delete($id)
    {
        Contact::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa liên hệ.');
    }

    public function render()
    {
        $contacts = Contact::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('message', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_handled', $this->statusFilter === '1'))
            ->orderBy('is_handled')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.contact-manager', [
            'contacts' => $contacts,
            'unhandledCount' => Contact::where('is_handled', false)->count(),
        ]);
    }
}
