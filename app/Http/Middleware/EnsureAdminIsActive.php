<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if ($admin?->is_active) {
            return $next($request);
        }

        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->withErrors([
            'email' => 'Akun admin ini tidak aktif. Hubungi super admin untuk bantuan.',
        ]);
    }
}
