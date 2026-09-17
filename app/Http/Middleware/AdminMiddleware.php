<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            return redirect()->route('admin.login');
        }

        abort_unless($admin->role === 'admin' && $admin->status === 'active', 403);

        return $next($request);
    }
}
