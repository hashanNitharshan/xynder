<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Presence
{
    /**
     * A user with no activity for this many seconds is marked offline.
     * The web panel and the mobile app send a heartbeat every 45 seconds.
     */
    public const TIMEOUT_SECONDS = 120;

    /**
     * The database is updated at most once in this many seconds per user,
     * so normal page clicks do not write to the users table every time.
     */
    public const WRITE_EVERY_SECONDS = 30;

    /**
     * Roles that are marked offline automatically after TIMEOUT_SECONDS.
     *
     * Only admins for now, so the current client and merchant behaviour
     * does not change. To use the same live logic for everyone, change to:
     * ['admin', 'client', 'merchant']
     */
    public const SWEEP_ROLES = ['admin'];

    /**
     * Mark the user as active now.
     * If the user chose "Go Offline", they stay offline.
     */
    public static function touch(?User $user, bool $force = false): void
    {
        if (! $user) {
            return;
        }

        if ($user->appear_offline) {
            if ($user->is_online) {
                $user->forceFill(['is_online' => false])->save();
            }

            return;
        }

        $recent = $user->is_online
            && $user->last_seen_at
            && $user->last_seen_at->gt(now()->subSeconds(self::WRITE_EVERY_SECONDS));

        if ($recent && ! $force) {
            return;
        }

        $user->forceFill([
            'is_online'    => true,
            'last_seen_at' => now(),
        ])->save();
    }

    /**
     * Mark the user as offline now (logout).
     */
    public static function offline(?User $user): void
    {
        if (! $user) {
            return;
        }

        $user->forceFill([
            'is_online'    => false,
            'last_seen_at' => $user->appear_offline ? $user->last_seen_at : now(),
        ])->save();
    }

    /**
     * Mark inactive users as offline.
     * Runs at most once per minute, so no cron job is needed.
     */
    public static function sweep(): void
    {
        if (! Cache::add('presence:sweep', 1, 60)) {
            return;
        }

        User::query()
            ->whereIn('role', self::SWEEP_ROLES)
            ->where('is_online', true)
            ->where(function ($query) {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', now()->subSeconds(self::TIMEOUT_SECONDS));
            })
            ->update(['is_online' => false]);
    }
}
