<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle($request, Closure $next, $role)
    {
        $user = auth()->user();

        if ($user && $user->role === $role) {
            return $next($request);
        }

        if ($role === 'cashier') {
            return redirect()->route('cashier'); // Redirect to cashier if trying to access unauthorized page
        }

        return abort(403, 'Unauthorized');
    }

}
