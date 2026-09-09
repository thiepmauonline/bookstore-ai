<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

#[Layout('components.layouts.admin')]
class UserManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $roleFilter = '';
    
    // Form fields
    public $user_id;
    public $name;
    public $email;
    public $phone;
    public $role = 'user';
    public $password;
    public $password_confirmation;
    
    public $isEditMode = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|in:user,admin',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['user_id', 'name', 'email', 'phone', 'role', 'password', 'password_confirmation']);
        $this->isEditMode = false;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('show-modal');
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'password' => Hash::make($this->password),
        ]);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm người dùng thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        
        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->user_id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,admin',
        ]);

        $user = User::findOrFail($this->user_id);
        
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
        ];

        // Only update password if provided
        if ($this->password) {
            $this->validate(['password' => 'required|string|min:8|confirmed']);
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật người dùng thành công!');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Không thể xóa tài khoản của chính bạn!');
            return;
        }

        $user->delete();
        session()->flash('message', 'Đã xóa người dùng thành công!');
    }

    public function render()
    {
        $query = User::where('role', 'user'); // Only show regular users, not admins

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        $users = $query->latest()->paginate(10);

        return view('livewire.admin.user-manager', [
            'users' => $users
        ]);
    }
}
