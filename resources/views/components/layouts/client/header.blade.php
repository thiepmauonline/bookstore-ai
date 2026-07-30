<div id="header-wrap">
    <div class="top-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="social-links">
                        <ul>
                            <li><a href="#"><i class="icon icon-facebook"></i></a></li>
                            <li><a href="#"><i class="icon icon-twitter"></i></a></li>
                            <li><a href="#"><i class="icon icon-youtube-play"></i></a></li>
                            <li><a href="#"><i class="icon icon-behance-square"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="right-element">
                        @auth
                            <a href="#" class="user-account for-buy"><i class="icon icon-user"></i><span>{{ auth()->user()->name }}</span></a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="user-account for-buy ms-2"><i class="icon icon-clipboard"></i><span>Quản trị</span></a>
                            @endif
                            <a href="{{ route('my.orders') }}" class="user-account for-buy ms-2"><i class="icon icon-shopping-bag"></i><span>Đơn hàng của tôi</span></a>
                            <a href="{{ route('logout') }}" class="user-account for-buy ms-2"><i class="icon icon-sign-out"></i><span>Đăng xuất</span></a>
                        @else
                            <a href="{{ route('login') }}" class="user-account for-buy"><i class="icon icon-user"></i><span>Tài khoản</span></a>
                        @endauth
                        
                        @php
                            $cartTotal = app(\App\Services\CartService::class)->getTotal();
                            $cartCount = app(\App\Services\CartService::class)->getCount();
                        @endphp
                        <a href="{{ route('cart') }}" class="cart for-buy ms-3">
                            <i class="icon icon-shopping-cart"></i>
                            <span>Giỏ hàng: {{ $cartCount }} ({{ number_format($cartTotal, 0, ',', '.') }}đ)</span>
                        </a>

                        <div class="action-menu">
                            <div class="search-bar">
                                <a href="#" class="search-button search-toggle" data-selector="#header-wrap">
                                    <i class="icon icon-search"></i>
                                </a>
                                <form role="search" method="get" class="search-box">
                                    <input class="search-field text search-input" placeholder="Tìm kiếm sách..." type="search">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <header id="header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2">
                    <div class="main-logo">
                        <a href="/"><img src="{{ asset('assets/client/images/main-logo.png') }}" alt="logo"></a>
                    </div>
                </div>
                <div class="col-md-10">
                    <nav id="navbar">
                        <div class="main-menu stellarnav">
                            <ul class="menu-list">
                                <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}"><a href="{{ route('home') }}">Trang chủ</a></li>
                                <li class="menu-item {{ request()->routeIs('about') ? 'active' : '' }}"><a href="{{ route('about') }}">Về chúng tôi</a></li>
                                <li class="menu-item"><a href="{{ route('home') }}#featured-books">Sản phẩm nổi bật</a></li>
                                <li class="menu-item {{ request()->routeIs('contact') ? 'active' : '' }}"><a href="{{ route('contact') }}">Liên hệ</a></li>
                            </ul>
                            <div class="hamburger">
                                <span class="bar"></span>
                                <span class="bar"></span>
                                <span class="bar"></span>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</div>
