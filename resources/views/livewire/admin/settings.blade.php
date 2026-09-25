<div>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading mb-4">
            <h1 class="h3 mb-1">Cài đặt cửa hàng</h1>
            <p class="text-muted mb-0">Thông tin hiển thị ở chân trang, trang Liên hệ và được chatbot dùng khi trả lời khách.</p>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="max-width: 760px">
            <form wire:submit="save" class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="store_name">Tên cửa hàng <span class="text-danger">*</span></label>
                        <input id="store_name" wire:model="form.store_name" type="text" class="form-control @error('form.store_name') is-invalid @enderror">
                        @error('form.store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="hotline">Hotline <span class="text-danger">*</span></label>
                        <input id="hotline" wire:model="form.hotline" type="text" class="form-control @error('form.hotline') is-invalid @enderror">
                        @error('form.hotline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email hỗ trợ <span class="text-danger">*</span></label>
                        <input id="email" wire:model="form.email" type="email" class="form-control @error('form.email') is-invalid @enderror">
                        @error('form.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Địa chỉ <span class="text-danger">*</span></label>
                        <input id="address" wire:model="form.address" type="text" class="form-control @error('form.address') is-invalid @enderror">
                        @error('form.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="working_hours">Giờ làm việc <span class="text-danger">*</span></label>
                        <input id="working_hours" wire:model="form.working_hours" type="text" class="form-control @error('form.working_hours') is-invalid @enderror">
                        @error('form.working_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-2"></span>
                        Lưu cài đặt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
