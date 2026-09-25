<nav class="navbar admin-navbar navbar-expand bg-white">
    <div class="container-fluid px-3 px-lg-4">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <form class="d-none d-md-flex ms-3 flex-grow-1" role="search" method="GET" action="{{ route('admin.orders') }}">
            <input class="form-control search-input" type="search" name="search" value="{{ request()->routeIs('admin.orders') ? request('search') : '' }}" placeholder="Tìm đơn hàng theo mã, tên hoặc SĐT khách..." aria-label="Tìm đơn hàng">
        </form>

        <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>
            @php
                $pendingOrders = \App\Models\Order::where('status', \App\Enums\OrderStatus::Pending)->count();
                $unhandledContacts = \App\Models\Contact::where('is_handled', false)->count();
            @endphp
            <div class="dropdown">
                <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo">
                    @if ($pendingOrders + $unhandledContacts > 0)
                        <span class="notification-dot"></span>
                    @endif
                    <i class="bi bi-bell" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu">
                    <div class="dropdown-header fw-bold text-body">Việc cần xử lý</div>
                    <a class="dropdown-item" href="{{ route('admin.orders', ['statusFilter' => 'pending']) }}">
                        <span class="notification-title">{{ $pendingOrders }} đơn hàng chờ xác nhận</span>
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.contacts') }}">
                        <span class="notification-title">{{ $unhandledContacts }} liên hệ chưa xử lý</span>
                    </a>
                </div>
            </div>

            <div class="dropdown">
                <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="avatar-img avatar-sm" src="{{ asset('assets/admin/images/avatar/avatar.jpg') }}" alt="Admin">
                    <span class="profile-name d-none d-sm-inline">{{ auth('admin')->user()->name ?? 'Administrator' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.profile') }}">Hồ sơ</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="dropdown-item">Đăng xuất</button></form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
