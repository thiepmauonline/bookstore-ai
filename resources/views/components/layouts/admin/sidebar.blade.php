<aside wire:navigate:scroll x-data="{ path: window.location.pathname }" x-on:livewire:navigated.window="path = window.location.pathname" class="admin-sidebar" id="adminSidebar" aria-label="Điều hướng chính">
    <div class="sidebar-header">
        <a wire:navigate class="brand-mark" href="{{ route('admin.dashboard') }}" aria-label="Quản trị Bookstore AI">
            <span class="brand-icon"><i class="bi bi-book-fill" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">Bookstore AI</span>
                <span class="brand-subtitle">Quản trị hệ thống</span>
            </span>
        </a>
    </div>

    <nav aria-label="Các mục quản trị">
    <ul class="sidebar-nav">
        <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.dashboard') }}">
            <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
            <span class="nav-text">Tổng quan</span>
        </a>
        </li>
        <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.books') }}">
            <span class="nav-icon"><i class="bi bi-book" aria-hidden="true"></i></span>
            <span class="nav-text">Quản lý sách</span>
        </a>
        </li>

        <li class="nav-heading">Thông tin sách</li>
        
        <li class="nav-item">
            <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.categories') }}">
                <span class="nav-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
                <span class="nav-text">Danh mục sách</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.authors') }}">
                <span class="nav-icon"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
                <span class="nav-text">Tác giả</span>
            </a>
        </li>

        <li class="nav-item">
            <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.publishers') }}">
                <span class="nav-icon"><i class="bi bi-building" aria-hidden="true"></i></span>
                <span class="nav-text">Nhà xuất bản</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.majors') }}">
                <span class="nav-icon"><i class="bi bi-journal-album" aria-hidden="true"></i></span>
                <span class="nav-text">Ngành học & học phần</span>
            </a>
        </li>

        <li class="nav-heading">Bán hàng</li>
    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.coupons') }}">
            <span class="nav-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
            <span class="nav-text">Mã giảm giá</span>
        </a>
    </li>
    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.users') }}">
            <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <span class="nav-text">Người dùng</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.orders') }}">
            <span class="nav-icon"><i class="bi bi-cart" aria-hidden="true"></i></span>
            <span class="nav-text">Đơn hàng</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.reviews') }}">
            <span class="nav-icon"><i class="bi bi-star" aria-hidden="true"></i></span>
            <span class="nav-text">Đánh giá</span>
        </a>
    </li>

    <li class="nav-heading">Chăm sóc khách hàng</li>
    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.chatbot') }}">
            <span class="nav-icon"><i class="bi bi-robot" aria-hidden="true"></i></span>
            <span class="nav-text">Chatbot</span>
        </a>
    </li>

    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.contacts') }}">
            <span class="nav-icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
            <span class="nav-text">Liên hệ</span>
        </a>
    </li>

    <li class="nav-item">
        <a wire:navigate class="nav-link" :class="path === new URL($el.href).pathname ? 'active' : ''" :aria-current="path === new URL($el.href).pathname ? 'page' : null" href="{{ route('admin.settings') }}">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Cài đặt</span>
        </a>
    </li>
    </ul>
    </nav>

    <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="{{ asset('assets/admin/images/avatar/avatar.jpg') }}" alt="Ảnh đại diện quản trị viên">
        <strong>{{ auth('admin')->user()->name ?? 'Quản trị viên' }}</strong>
        <small>Quản trị viên</small>
    </div>

    <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">Hệ thống ổn định</span>
    </div>
</aside>
