<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = collect(explode('|', $roles))
            ->contains(fn($r) => trim($user->role) === trim($r));

        if (!$allowed) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
