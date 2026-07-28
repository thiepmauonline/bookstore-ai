<?php

$dirLivewire = __DIR__ . '/app/Livewire/Admin/';
$dirViews = __DIR__ . '/resources/views/livewire/admin/';

if (!is_dir($dirLivewire)) mkdir($dirLivewire, 0777, true);
if (!is_dir($dirViews)) mkdir($dirViews, 0777, true);

function makeClass($name, $model, $fields, $rules) {
    $fieldDecl = implode(";\n    public \$", array_keys($fields));
    $fieldDecl = "public \$" . $fieldDecl . ";";
    $resetFields = "'" . implode("', '", array_merge(['model_id'], array_keys($fields))) . "'";
    
    $storeAssign = "";
    $editAssign = "";
    foreach(array_keys($fields) as $f) {
        $storeAssign .= "'$f' => \$this->$f,\n            ";
        $editAssign .= "\$this->$f = \$item->$f;\n        ";
    }

    $rulesStr = var_export($rules, true);

    return <<<EOT
<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\\$model;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
class {$name} extends Component
{
    use WithPagination;
    protected \$paginationTheme = 'bootstrap';

    public \$search = '';
    public \$model_id;
    $fieldDecl
    
    public \$isEditMode = false;

    protected \$rules = $rulesStr;

    public function updatedSearch()
    {
        \$this->resetPage();
    }

    public function updatedName()
    {
        // Auto generate slug if slug field exists
        if (property_exists(\$this, 'slug') && !\$this->isEditMode) {
            \$this->slug = Str::slug(\$this->name);
        }
    }

    public function resetForm()
    {
        \$this->reset([$resetFields]);
        \$this->isEditMode = false;
        \$this->resetErrorBag();
    }

    public function create()
    {
        \$this->resetForm();
        \$this->dispatch('show-modal');
    }

    public function store()
    {
        \$this->validate();
        \\App\\Models\\$model::create([
            $storeAssign
        ]);
        \$this->dispatch('hide-modal');
        session()->flash('message', 'Thêm mới thành công!');
    }

    public function edit(\$id)
    {
        \$this->resetForm();
        \$this->isEditMode = true;
        
        \$item = \\App\\Models\\$model::findOrFail(\$id);
        \$this->model_id = \$item->id;
        $editAssign
        \$this->dispatch('show-modal');
    }

    public function update()
    {
        \$this->validate();
        \$item = \\App\\Models\\$model::findOrFail(\$this->model_id);
        \$item->update([
            $storeAssign
        ]);
        \$this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật thành công!');
    }

    public function delete(\$id)
    {
        // For restrict relationships, this might fail if books exist. Catch it?
        try {
            \\App\\Models\\$model::findOrFail(\$id)->delete();
            session()->flash('message', 'Đã xóa thành công!');
        } catch (\Exception \$e) {
            session()->flash('error', 'Không thể xóa vì đang có Sách tham chiếu đến dữ liệu này!');
        }
    }

    public function render()
    {
        \$items = \\App\\Models\\$model::where('name', 'like', '%' . \$this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-\$0', '$name')), [
            'items' => \$items
        ]);
    }
}
EOT;
}

function makeView($name, $modelTitle, $fields) {
    $tableHeaders = "";
    $tableCells = "";
    $formInputs = "";
    
    foreach($fields as $k => $label) {
        $tableHeaders .= "<th scope=\"col\">$label</th>\n";
        $tableCells .= "<td>{{ \$item->$k }}</td>\n";
        
        if ($k == 'biography' || $k == 'address') {
            $formInputs .= <<<HTML
                            <div class="col-12">
                                <label class="form-label">$label</label>
                                <textarea wire:model="$k" class="form-control @error('$k') is-invalid @enderror" rows="3"></textarea>
                                @error('$k') <div class="invalid-feedback">{{ \$message }}</div> @enderror
                            </div>
HTML;
        } else {
            $required = $k == 'name' || $k == 'slug' ? '<span class="text-danger">*</span>' : '';
            $formInputs .= <<<HTML
                            <div class="col-md-12">
                                <label class="form-label">$label $required</label>
                                <input wire:model="$k" type="text" class="form-control @error('$k') is-invalid @enderror">
                                @error('$k') <div class="invalid-feedback">{{ \$message }}</div> @enderror
                            </div>
HTML;
        }
    }

    $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

    return <<<EOT
<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading d-flex justify-content-between align-items-center mb-4">
            <div class="page-heading-copy">
                <div>
                    <h1 class="h3 mb-1">Quản lý $modelTitle</h1>
                </div>
            </div>
            <div>
                <button wire:click="create" class="btn btn-primary" type="button">
                    <i class="bi bi-plus-lg"></i> Thêm mới
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 bg-light" placeholder="Tìm kiếm...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">ID</th>
                            $tableHeaders
                            <th scope="col" class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (\$items as \$item)
                            <tr>
                                <td class="ps-4">{{ \$item->id }}</td>
                                $tableCells
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ \$item->id }})" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="delete({{ \$item->id }})" wire:confirm="Bạn có chắc chắn muốn xóa?" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">{{ \$items->links() }}</div>
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="crudModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="{{ \$isEditMode ? 'update' : 'store' }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ \$isEditMode ? 'Cập nhật' : 'Thêm mới' }} $modelTitle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            $formInputs
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="{{ \$isEditMode ? 'update' : 'store' }}" class="spinner-border spinner-border-sm me-2"></span>
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let myModal = new bootstrap.Modal(document.getElementById('crudModal'));
            Livewire.on('show-modal', () => { myModal.show(); });
            Livewire.on('hide-modal', () => { 
                myModal.hide(); 
                document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    </script>
</div>
EOT;
}

// 1. CategoryManager
$catClass = makeClass('CategoryManager', 'Category', ['name' => 'Tên', 'slug' => 'Slug'], ['name' => 'required|string|max:255', 'slug' => 'required|string|max:255']);
$catView = makeView('CategoryManager', 'Danh mục', ['name' => 'Tên danh mục', 'slug' => 'Slug (Đường dẫn SEO)']);
file_put_contents($dirLivewire . 'CategoryManager.php', str_replace('\\$', '$', $catClass));
file_put_contents($dirViews . 'category-manager.blade.php', str_replace('\\$', '$', $catView));

// 2. AuthorManager
$authClass = makeClass('AuthorManager', 'Author', ['name' => 'Tên', 'biography' => 'Tiểu sử'], ['name' => 'required|string|max:255', 'biography' => 'nullable|string']);
$authView = makeView('AuthorManager', 'Tác giả', ['name' => 'Tên tác giả', 'biography' => 'Tiểu sử / Ghi chú']);
file_put_contents($dirLivewire . 'AuthorManager.php', str_replace('\\$', '$', $authClass));
file_put_contents($dirViews . 'author-manager.blade.php', str_replace('\\$', '$', $authView));

// 3. PublisherManager
$pubClass = makeClass('PublisherManager', 'Publisher', ['name' => 'Tên', 'address' => 'Địa chỉ'], ['name' => 'required|string|max:255', 'address' => 'nullable|string']);
$pubView = makeView('PublisherManager', 'Nhà Xuất Bản', ['name' => 'Tên nhà xuất bản', 'address' => 'Địa chỉ liên hệ']);
file_put_contents($dirLivewire . 'PublisherManager.php', str_replace('\\$', '$', $pubClass));
file_put_contents($dirViews . 'publisher-manager.blade.php', str_replace('\\$', '$', $pubView));

echo "Generated Category, Author, Publisher Managers.\n";
?>
