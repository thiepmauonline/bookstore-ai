<div class="announcement">Một cuốn sách mới. Một thế giới mới. <span>Khám phá cùng Bookstore AI</span></div>
<header class="store-header">
    <div class="container header-main">
        <a href="{{ route('home') }}" class="store-brand" aria-label="Bookstore AI - Trang chủ"><span class="brand-icon"><i class="bi bi-book"></i></span><span>bookstore<span class="brand-ai">ai.</span><small>ĐỌC ĐỂ MỞ RỘNG THẾ GIỚI</small></span></a>
        <form action="{{ route('home') }}#featured-books" method="get" class="header-search" role="search"><i class="bi bi-search"></i><input name="search" type="search" value="{{ request('search') }}" placeholder="Tìm cuốn sách tiếp theo của bạn…" aria-label="Tìm sách theo tên"><button type="submit" aria-label="Tìm kiếm"><i class="bi bi-arrow-right"></i></button></form>
        <div class="header-actions">
            @auth
                <div class="dropdown"><button aria-label="Menu tài khoản" class="account-button dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person"></i><span>Tài khoản</span></button><ul class="dropdown-menu dropdown-menu-end"><li class="dropdown-header">{{ auth()->user()->name }}</li><li><a class="dropdown-item" href="{{ route('my.orders') }}">Đơn hàng của tôi</a></li><li><a class="dropdown-item" href="{{ route('wishlist') }}">Sách yêu thích</a></li>@if(auth()->user()->role === 'admin')<li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Quản trị</a></li>@endif<li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Đăng xuất</button></form></li></ul></div>
            @else
                <a href="{{ route('login') }}" class="account-button" aria-label="Đăng nhập"><i class="bi bi-person"></i><span>Đăng nhập</span></a>
            @endauth
            <livewire:client.cart-indicator />
        </div>
    </div>
    <nav class="container store-nav" aria-label="Điều hướng chính"><a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a><a href="{{ route('home') }}#featured-books">Tất cả sách</a><a href="{{ route('home') }}#categories">Danh mục sách</a><a href="{{ route('about') }}">Về chúng tôi</a><a href="{{ route('contact') }}">Liên hệ</a><span class="nav-note"><i class="bi bi-book-half"></i> Nuôi dưỡng thói quen đọc mỗi ngày</span></nav>
</header>
