<div>
    <section class="padding-large">
        <div class="container">
            <div class="row">
                <div class="col-md-6 pe-lg-5 mb-5 mb-md-0">
                    <h2 class="display-5 mb-4">Liên hệ với chúng tôi</h2>
                    <p class="mb-4 text-muted">Nếu bạn có bất kỳ câu hỏi nào về hệ thống, vui lòng để lại lời nhắn. Đội ngũ Bookstore-AI sẽ liên hệ lại với bạn trong thời gian sớm nhất.</p>
                    
                    <ul class="list-unstyled mb-5">
                        <li class="d-flex mb-3">
                            <i class="icon icon-map-marker fs-4 text-primary me-3"></i>
                            <span>{{ $settings['address'] }}</span>
                        </li>
                        <li class="d-flex mb-3">
                            <i class="icon icon-phone fs-4 text-primary me-3"></i>
                            <span>{{ $settings['hotline'] }}</span>
                        </li>
                        <li class="d-flex">
                            <i class="icon icon-envelope fs-4 text-primary me-3"></i>
                            <span>{{ $settings['email'] }}</span>
                        </li>
                        <li class="d-flex mt-3">
                            <i class="bi bi-clock fs-4 text-primary me-3"></i>
                            <span>{{ $settings['working_hours'] }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light p-4 p-lg-5">
                        <h4 class="mb-4">Gửi tin nhắn</h4>
                        @if (session()->has('contact_message'))
                            <div class="alert alert-success">{{ session('contact_message') }}</div>
                        @endif
                        <form wire:submit="submit">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control bg-white @error('name') is-invalid @enderror" placeholder="Nhập tên của bạn">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input wire:model="email" type="email" class="form-control bg-white @error('email') is-invalid @enderror" placeholder="Nhập email của bạn">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea wire:model="message" class="form-control bg-white @error('message') is-invalid @enderror" rows="5" placeholder="Bạn cần hỗ trợ gì?"></textarea>
                                @error('message') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-5 py-2">
                                <span wire:loading.remove wire:target="submit">Gửi tin nhắn</span>
                                <span wire:loading wire:target="submit">Đang gửi...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
