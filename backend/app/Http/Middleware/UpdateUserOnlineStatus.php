<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateUserOnlineStatus
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $request->user()->forceFill([
                'is_online' => true,
                'last_seen_at' => now(),
            ])->save();
        }

        return $next($request);
    }
}