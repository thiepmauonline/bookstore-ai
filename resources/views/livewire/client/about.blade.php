<div>
    <section class="padding-large">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 pe-5">
                    <h2 class="display-5 mb-4">Về Bookstore-AI</h2>
                    <p class="lead mb-4">Bookstore-AI là dự án hệ thống thương mại điện tử chuyên cung cấp sách và giáo trình phục vụ cho nhu cầu học tập của sinh viên mọi khối ngành.</p>
                    <p class="mb-4">Chúng tôi hiểu rằng việc tìm kiếm đúng cuốn giáo trình phù hợp với học phần của mình luôn là một khó khăn lớn. Với việc tích hợp Chatbot AI tiên tiến, Bookstore-AI không chỉ bán sách mà còn hoạt động như một người cố vấn học tập thực thụ, giúp bạn định hướng và lựa chọn tài liệu phù hợp nhất.</p>
                    <div class="d-flex mb-4">
                        <div class="me-4 text-center">
                            <h3 class="text-primary mb-1">{{ $bookCount }}</h3>
                            <span class="text-muted small">Đầu sách</span>
                        </div>
                        <div class="me-4 text-center">
                            <h3 class="text-primary mb-1">{{ $majorCount }}</h3>
                            <span class="text-muted small">Ngành · {{ $courseCount }} học phần</span>
                        </div>
                        <div class="text-center">
                            <h3 class="text-primary mb-1">24/7</h3>
                            <span class="text-muted small">AI Hỗ trợ</span>
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
