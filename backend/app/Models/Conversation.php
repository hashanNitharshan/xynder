<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'wallet_transfer_id',
        'wallet_request_id',
        'chat_started_at',
        'locked_at',
        'lock_reason',
    ];

    protected $casts = [
        'chat_started_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function userOne() { return $this->belongsTo(User::class, 'user_one_id'); }
    public function userTwo() { return $this->belongsTo(User::class, 'user_two_id'); }
    public function walletTransfer() { return $this->belongsTo(WalletTransfer::class, 'wallet_transfer_id'); }
    public function walletRequest() { return $this->belongsTo(WalletRequest::class, 'wallet_request_id'); }
    public function messages() { return $this->hasMany(ChatMessage::class); }

    public function isLocked(): bool
    {
        $this->lockIfExpired();
        return ! is_null($this->locked_at);
    }

    /**
     * Single source of truth for auto-locking. Call this on every
     * fetch/send from ANY controller (API or Web) — the logic lives
     * here once, not duplicated per controller.
     *
     * - Request-linked conversations:
     *   1. If the linked WalletRequest has sat "pending" for 10+ minutes,
     *      it's force-closed here.
     *   2. Once the request is approved / rejected / closed (by anyone,
     *      anywhere), the chat locks on the next check.
     *   NOTE: we always re-query the WalletRequest directly instead of
     *   using the cached Eloquent relation, so a status change made in
     *   one request is seen immediately even if this Conversation was
     *   eager-loaded earlier in the same request lifecycle.
     *
     * - Transfer-linked conversations: independent 15-minute timer
     *   starting from chat_started_at.
     */
    public function lockIfExpired(): void
    {
        if ($this->locked_at) {
            return;
        }

        if ($this->wallet_request_id) {
            $walletRequest = $this->walletRequest()->first();

            if ($walletRequest) {
                $status = strtolower((string) $walletRequest->status);

                if ($status === 'pending'
                    && $walletRequest->created_at
                    && now()->diffInMinutes($walletRequest->created_at) >= 10) {
                    $walletRequest->update(['status' => 'closed']);
                    $status = 'closed';
                }

                if (in_array($status, ['approved', 'rejected', 'closed'], true)) {
                    $this->forceFill([
                        'locked_at' => now(),
                        'lock_reason' => 'request_' . $status,
                    ])->save();
                }

                $this->setRelation('walletRequest', $walletRequest);
            }

            return;
        }

        if ($this->wallet_transfer_id && $this->chat_started_at
            && $this->chat_started_at->copy()->addMinutes(15)->isPast()) {
            $this->forceFill([
                'locked_at' => now(),
                'lock_reason' => 'timeout_15_minutes',
            ])->save();
        }
    }

    public function remainingSeconds(): int
    {
        if ($this->isLocked()) {
            return 0;
        }

        // Request-linked: countdown is 10 minutes from the request's creation time.
        if ($this->wallet_request_id) {
            $walletRequest = $this->walletRequest()->first();

            if (! $walletRequest || ! $walletRequest->created_at) {
                return 0;
            }

            $deadline = $walletRequest->created_at->copy()->addMinutes(10);
            return max(0, (int) now()->diffInSeconds($deadline, false));
        }

        // Transfer-linked: countdown is 15 minutes from chat start.
        if (! $this->chat_started_at) {
            return 0;
        }

        $deadline = $this->chat_started_at->copy()->addMinutes(15);
        return max(0, (int) now()->diffInSeconds($deadline, false));
    }
}