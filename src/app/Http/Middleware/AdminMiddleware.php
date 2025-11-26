<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!($user instanceof User) || !$user->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
