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
        session()->flash('message', 'Đã thêm người dùng.');
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

        // Tránh admin tự hạ quyền của mình rồi bị khóa khỏi trang quản trị.
        if ($user->id === auth('admin')->id() && $this->role !== 'admin') {
            $this->addError('role', 'Không thể bỏ quyền quản trị của chính mình.');
            return;
        }

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
        session()->flash('message', 'Đã cập nhật người dùng.');
    }

    /** Khóa hoặc mở khóa tài khoản; tài khoản bị khóa không đăng nhập được. */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth('admin')->id()) {
            session()->flash('error', 'Không thể khóa tài khoản của chính mình.');
            return;
        }

        $user->update(['status' => $user->status === 'active' ? 'banned' : 'active']);

        session()->flash('message', $user->status === 'active'
            ? "Đã mở khóa tài khoản {$user->email}."
            : "Đã khóa tài khoản {$user->email}.");
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth('admin')->id()) {
            session()->flash('error', 'Không thể xóa tài khoản của chính mình.');
            return;
        }

        // Giữ lại lịch sử đơn hàng: khách đã mua hàng thì chỉ được khóa, không được xóa.
        if ($user->orders()->exists()) {
            session()->flash('error', 'Không thể xóa tài khoản đã có đơn hàng. Hãy khóa tài khoản nếu cần ngừng truy cập.');
            return;
        }

        $user->delete();
        session()->flash('message', 'Đã xóa người dùng.');
    }

    public function render()
    {
        $query = User::query();

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
