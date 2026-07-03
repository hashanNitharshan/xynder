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
     * Single source of truth for auto-locking.
     *
     * - Request-linked conversations: lock state is driven ENTIRELY by the
     *   linked WalletRequest's status. As soon as a request is approved,
     *   rejected, or closed (by admin, merchant, or client — any controller),
     *   the chat locks the next time this is checked. No controller needs to
     *   call anything explicitly.
     *
     * - Transfer-linked conversations: unrelated feature, keeps its own
     *   15-minute-from-first-message timer.
     */
    public function lockIfExpired(): void
    {
        if ($this->locked_at) {
            return;
        }

        if ($this->wallet_request_id) {
            $this->loadMissing('walletRequest');

            if ($this->walletRequest && in_array($this->walletRequest->status, ['approved', 'rejected', 'closed'], true)) {
                $this->forceFill([
                    'locked_at' => now(),
                    'lock_reason' => 'request_' . $this->walletRequest->status,
                ])->save();
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
            $this->loadMissing('walletRequest');

            if (! $this->walletRequest || ! $this->walletRequest->created_at) {
                return 0;
            }

            $deadline = $this->walletRequest->created_at->copy()->addMinutes(10);
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