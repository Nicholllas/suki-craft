<?php

namespace App\Http\Middleware;

use App\Enums\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user('admin')?->role;

        if ($role instanceof AdminRole && in_array($role->value, $roles, true)) {
            return $next($request);
        }

        abort(403);
    }
}
