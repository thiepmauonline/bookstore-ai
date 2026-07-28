<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Major;
use App\Models\Course;

#[Layout('components.layouts.admin')]
class MajorManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    
    // Major Fields
    public $major_id;
    public $name;
    public $isEditMode = false;

    // Course Fields
    public $managingCoursesFor = null;
    public $course_id;
    public $course_name;
    public $isCourseEditMode = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['major_id', 'name']);
        $this->isEditMode = false;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('show-major-modal');
    }

    public function store()
    {
        $this->validate(['name' => 'required|string|max:255']);
        Major::create(['name' => $this->name]);
        $this->dispatch('hide-major-modal');
        session()->flash('message', 'Thêm ngành học thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $item = Major::findOrFail($id);
        $this->major_id = $item->id;
        $this->name = $item->name;
        $this->dispatch('show-major-modal');
    }

    public function update()
    {
        $this->validate(['name' => 'required|string|max:255']);
        $item = Major::findOrFail($this->major_id);
        $item->update(['name' => $this->name]);
        $this->dispatch('hide-major-modal');
        session()->flash('message', 'Cập nhật ngành học thành công!');
    }

    public function delete($id)
    {
        try {
            Major::findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa ngành học thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa vì đang có Học phần liên kết!');
        }
    }

    // --- Course Management ---
    public function manageCourses($majorId)
    {
        $this->managingCoursesFor = Major::with('courses')->findOrFail($majorId);
        $this->resetCourseForm();
        $this->dispatch('show-courses-modal');
    }

    public function resetCourseForm()
    {
        $this->reset(['course_id', 'course_name']);
        $this->isCourseEditMode = false;
        $this->resetErrorBag();
    }

    public function editCourse($courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->course_id = $course->id;
        $this->course_name = $course->name;
        $this->isCourseEditMode = true;
        $this->resetErrorBag();
    }

    public function saveCourse()
    {
        $this->validate(['course_name' => 'required|string|max:255']);

        if ($this->isCourseEditMode) {
            $course = Course::findOrFail($this->course_id);
            $course->update(['name' => $this->course_name]);
            session()->flash('course_message', 'Cập nhật học phần thành công!');
        } else {
            Course::create([
                'major_id' => $this->managingCoursesFor->id,
                'name' => $this->course_name
            ]);
            session()->flash('course_message', 'Thêm học phần thành công!');
        }

        $this->managingCoursesFor->load('courses'); // Refresh relationships
        $this->resetCourseForm();
    }

    public function deleteCourse($courseId)
    {
        try {
            Course::findOrFail($courseId)->delete();
            $this->managingCoursesFor->load('courses');
            session()->flash('course_message', 'Đã xóa học phần!');
        } catch (\Exception $e) {
            session()->flash('course_error', 'Không thể xóa vì đang có sách tham chiếu!');
        }
    }

    public function render()
    {
        $majors = Major::withCount('courses')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.major-manager', [
            'majors' => $majors
        ]);
    }
}
