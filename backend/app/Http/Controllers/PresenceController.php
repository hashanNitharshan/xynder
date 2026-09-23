<?php

namespace App\Http\Controllers;

use App\Support\Presence;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    /**
     * Web panel heartbeat (sent every 45 seconds by the layout script).
     */
    public function heartbeat(Request $request)
    {
        $user = $request->user();

        Presence::touch($user, true);
        Presence::sweep();

        $user->refresh();

        return response()->json([
            'success'        => true,
            'is_online'      => (bool) $user->is_online,
            'appear_offline' => (bool) $user->appear_offline,
        ]);
    }

    /**
     * Admin web option: Go Online / Go Offline.
     */
    public function toggle(Request $request)
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'admin', 403);

        $hide = ! $user->appear_offline;

        $user->forceFill([
            'appear_offline' => $hide,
            'is_online'      => ! $hide,
            'last_seen_at'   => now(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success'        => true,
                'is_online'      => ! $hide,
                'appear_offline' => $hide,
            ]);
        }

        return back()->with('success', $hide ? 'You are now offline.' : 'You are now online.');
    }

    /**
     * Mobile app heartbeat: POST /api/ping
     */
    public function apiPing(Request $request)
    {
        $user = $request->user();

        Presence::touch($user, true);
        Presence::sweep();

        $user->refresh();

        return response()->json([
            'success'        => true,
            'is_online'      => (bool) $user->is_online,
            'appear_offline' => (bool) $user->appear_offline,
        ]);
    }

    /**
     * Mobile app option: POST /api/presence/toggle (admin only).
     */
    public function apiToggle(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can change this option.',
            ], 403);
        }

        $hide = ! $user->appear_offline;

        $user->forceFill([
            'appear_offline' => $hide,
            'is_online'      => ! $hide,
            'last_seen_at'   => now(),
        ])->save();

        return response()->json([
            'success'        => true,
            'message'        => $hide ? 'You are now offline.' : 'You are now online.',
            'is_online'      => ! $hide,
            'appear_offline' => $hide,
        ]);
    }
}
