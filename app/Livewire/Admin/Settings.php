<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Settings extends Component
{
    /** @var array<string, string|null> */
    public array $form = [];

    public function mount()
    {
        $this->form = array_intersect_key(Setting::allValues(), Setting::DEFAULTS);
    }

    public function save()
    {
        $this->validate([
            'form.store_name' => 'required|string|max:100',
            'form.hotline' => ['required', 'string', 'max:20', 'regex:/^[0-9+ .()-]+$/'],
            'form.email' => 'required|email|max:255',
            'form.address' => 'required|string|max:255',
            'form.working_hours' => 'required|string|max:100',
        ], [
            'required' => 'Vui lòng nhập :attribute.',
            'form.email.email' => 'Email không hợp lệ.',
            'form.hotline.regex' => 'Hotline chỉ gồm chữ số và các ký tự + . ( ) -',
        ], [
            'form.store_name' => 'tên cửa hàng',
            'form.hotline' => 'hotline',
            'form.email' => 'email',
            'form.address' => 'địa chỉ',
            'form.working_hours' => 'giờ làm việc',
        ]);

        foreach (array_keys(Setting::DEFAULTS) as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => trim((string) $this->form[$key])]);
        }

        session()->flash('message', 'Đã lưu cài đặt cửa hàng.');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
