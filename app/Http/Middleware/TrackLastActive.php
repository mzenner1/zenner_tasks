<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Update at most once per minute to avoid excessive writes
            if (is_null($user->last_active_at) || $user->last_active_at->diffInMinutes(now()) >= 1) {
                $user->updateQuietly(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
