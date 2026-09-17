<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function client(Request $request): RedirectResponse
    {
        return $this->logout($request, 'web', 'login');
    }

    public function admin(Request $request): RedirectResponse
    {
        return $this->logout($request, 'admin', 'admin.login');
    }

    private function logout(Request $request, string $guard, string $route): RedirectResponse
    {
        Auth::guard($guard)->logout();
        // Rotate the session ID without deleting the other guard's login or cart.
        $request->session()->migrate(true);

        return redirect()->route($route);
    }
}
