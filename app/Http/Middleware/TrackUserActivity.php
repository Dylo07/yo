<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackUserActivity
{
    /**
     * Track authenticated user's last activity.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->last_seen_at = Carbon::now();
            $user->last_page = $request->path();
            $user->last_ip = $request->ip();
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;
        }

        return $next($request);
    }
}
