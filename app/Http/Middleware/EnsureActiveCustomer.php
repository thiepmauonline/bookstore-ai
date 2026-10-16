<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = auth('web');
        if ($user = $guard->user()) {
            $current = $user->fresh();
            if (! $current || $current->role !== 'user' || $current->status !== 'active') {
                $guard->logout();
                // Giữ giỏ hàng và phiên quản trị đang đăng nhập cùng trình duyệt.
                $request->session()->migrate(true);

                return redirect()->route('login')->with('message', 'Tài khoản của bạn đã bị khóa hoặc không còn quyền truy cập. Vui lòng liên hệ cửa hàng.');
            }
        }

        return $next($request);
    }
}
