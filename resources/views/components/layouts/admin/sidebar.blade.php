<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="{{ route('admin.dashboard') }}" aria-label="Bookstore AI admin">
            <span class="brand-icon"><i class="bi bi-book-fill" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">Bookstore AI</span>
                <span class="brand-subtitle">Quản trị hệ thống</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" aria-current="page">
            <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>
        <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.books') ? 'active' : '' }}" href="{{ route('admin.books') }}">
            <span class="nav-icon"><i class="bi bi-book" aria-hidden="true"></i></span>
            <span class="nav-text">Quản lý Sách</span>
        </a>
        </li>

        <li class="nav-heading">Thuộc tính Sách</li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}" href="{{ route('admin.categories') }}">
                <span class="nav-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
                <span class="nav-text">Danh mục Sách</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.authors') ? 'active' : '' }}" href="{{ route('admin.authors') }}">
                <span class="nav-icon"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
                <span class="nav-text">Tác giả</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.publishers') ? 'active' : '' }}" href="{{ route('admin.publishers') }}">
                <span class="nav-icon"><i class="bi bi-building" aria-hidden="true"></i></span>
                <span class="nav-text">Nhà xuất bản</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.majors') ? 'active' : '' }}" href="{{ route('admin.majors') }}">
                <span class="nav-icon"><i class="bi bi-journal-album" aria-hidden="true"></i></span>
                <span class="nav-text">Ngành học & Môn học</span>
            </a>
        </li>

        <li class="nav-heading">Bán hàng</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.coupons') ? 'active' : '' }}" href="{{ route('admin.coupons') }}">
            <span class="nav-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
            <span class="nav-text">Mã giảm giá</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
            <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <span class="nav-text">Người dùng</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}" href="{{ route('admin.orders') }}">
            <span class="nav-icon"><i class="bi bi-cart" aria-hidden="true"></i></span>
            <span class="nav-text">Đơn hàng</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}" href="{{ route('admin.reviews') }}">
            <span class="nav-icon"><i class="bi bi-star" aria-hidden="true"></i></span>
            <span class="nav-text">Đánh giá</span>
        </a>
    </li>

    <li class="nav-heading">Chăm sóc khách hàng</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.chatbot') ? 'active' : '' }}" href="{{ route('admin.chatbot') }}">
            <span class="nav-icon"><i class="bi bi-robot" aria-hidden="true"></i></span>
            <span class="nav-text">Dữ liệu AI Chatbot</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.contacts') ? 'active' : '' }}" href="{{ route('admin.contacts') }}">
            <span class="nav-icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <span class="nav-text">Liên hệ</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Cài đặt</span>
        </a>
    </li>
    </nav>

    <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="{{ asset('assets/admin/images/avatar/avatar.jpg') }}" alt="Admin">
        <strong>{{ auth('admin')->user()->name ?? 'Administrator' }}</strong>
        <small>Quản trị viên</small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">Hệ thống ổn định</span>
    </div>
</aside>
