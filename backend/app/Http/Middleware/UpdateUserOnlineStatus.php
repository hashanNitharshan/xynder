<?php

namespace App\Http\Middleware;

use App\Support\Presence;
use Closure;
use Illuminate\Http\Request;

class UpdateUserOnlineStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            Presence::touch($user);
        }

        Presence::sweep();

        return $next($request);
    }
}
