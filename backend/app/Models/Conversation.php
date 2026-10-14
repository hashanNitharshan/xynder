<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Conversation extends Model
{
    /**
     * How long a chat stays open after it starts (both request and
     * transfer chats). 24 hours = 1440 minutes.
     */
    public const CHAT_LIFETIME_MINUTES = 1440;

    /**
     * A wallet request that stays "pending" this long is closed
     * automatically. This is a request rule, not a chat rule: the
     * chat itself still stays open for 24 hours.
     */
    public const PENDING_REQUEST_TIMEOUT_MINUTES = 10;

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
     * When the 24 hour chat window started. Uses chat_started_at, and
     * falls back to created_at for older conversations without it.
     */
    public function chatStart(): ?Carbon
    {
        return $this->chat_started_at ?? $this->created_at;
    }

    /**
     * When the chat locks (start + 24 hours).
     */
    public function chatDeadline(): ?Carbon
    {
        $start = $this->chatStart();

        return $start
            ? $start->copy()->addMinutes(self::CHAT_LIFETIME_MINUTES)
            : null;
    }

    /**
     * Single source of truth for auto-locking. Called on every
     * fetch/send from ANY controller (API or Web).
     *
     * - Every chat (request or transfer) locks 24 hours after it starts.
     * - The status of the wallet request no longer locks the chat, so
     *   both sides can keep talking after approve / reject / close.
     * - A wallet request that stays "pending" for 10+ minutes is still
     *   closed here (request rule only, the chat stays open).
     *   We always re-query the WalletRequest so a status change made
     *   elsewhere is seen at once.
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
                    && $walletRequest->created_at->copy()
                        ->addMinutes(self::PENDING_REQUEST_TIMEOUT_MINUTES)
                        ->isPast()) {
                    $walletRequest->update(['status' => 'closed']);
                }

                $this->setRelation('walletRequest', $walletRequest);
            }
        }

        $deadline = $this->chatDeadline();

        if ($deadline && $deadline->isPast()) {
            $this->forceFill([
                'locked_at' => now(),
                'lock_reason' => 'timeout_24_hours',
            ])->save();
        }
    }

    /**
     * Seconds left before the chat locks. 0 when locked.
     */
    public function remainingSeconds(): int
    {
        if ($this->isLocked()) {
            return 0;
        }

        $deadline = $this->chatDeadline();

        if (! $deadline) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($deadline, false));
    }
}