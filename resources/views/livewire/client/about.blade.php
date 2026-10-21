<div>
    <section class="padding-large">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 pe-5">
                    <h2 class="display-5 mb-4">Về Bookstore AI</h2>
                    <p class="lead mb-4">Bookstore AI cung cấp sách và giáo trình cho sinh viên nhiều ngành học.</p>
                    <p class="mb-4">Tìm sách theo ngành học, học phần hoặc trao đổi với trợ lý AI để chọn tài liệu phù hợp.</p>
                    <div class="d-flex mb-4">
                        <div class="me-4 text-center">
                            <h3 class="text-primary mb-1">{{ $bookCount }}</h3>
                            <span class="text-muted small">Đầu sách</span>
                        </div>
                        <div class="me-4 text-center">
                            <h3 class="text-primary mb-1">{{ $majorCount }}</h3>
                            <span class="text-muted small">Ngành học · {{ $courseCount }} học phần</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-primary mb-1">24/7</h3>
                            <span class="text-muted small">Trợ lý AI</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="{{ asset('assets/client/images/main-banner1.jpg') }}" alt="Về chúng tôi" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>
</div>
