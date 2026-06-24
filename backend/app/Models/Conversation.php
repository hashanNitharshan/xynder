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

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function walletTransfer()
    {
        return $this->belongsTo(WalletTransfer::class, 'wallet_transfer_id');
    }

    public function walletRequest()
    {
        return $this->belongsTo(WalletRequest::class, 'wallet_request_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    private function shouldApplyLock(): bool
    {
        if (! $this->wallet_request_id) {
            return false;
        }

        $request = $this->walletRequest;

        if (! $request) {
            return false;
        }

        return $request->status === 'pending';
    }

    private function clearLockIfNotPending(): void
    {
        if ($this->shouldApplyLock()) {
            return;
        }

        if ($this->locked_at || $this->lock_reason) {
            $this->update([
                'locked_at' => null,
                'lock_reason' => null,
            ]);

            $this->refresh();
        }
    }

    public function isLocked(): bool
    {
        $this->clearLockIfNotPending();

        if (! $this->shouldApplyLock()) {
            return false;
        }

        if ($this->locked_at) {
            return true;
        }

        if (! $this->chat_started_at) {
            return false;
        }

        return $this->chat_started_at->copy()->addMinutes(15)->isPast();
    }

    public function lockIfExpired(): void
    {
        $this->clearLockIfNotPending();

        if (! $this->shouldApplyLock()) {
            return;
        }

        if ($this->locked_at || ! $this->chat_started_at) {
            return;
        }

        if ($this->chat_started_at->copy()->addMinutes(15)->isPast()) {
            $this->update([
                'locked_at' => now(),
                'lock_reason' => 'Pending request chat locked automatically after 15 minutes.',
            ]);
        }
    }

    public function remainingSeconds(): int
    {
        $this->clearLockIfNotPending();

        if (! $this->shouldApplyLock()) {
            return 0;
        }

        if ($this->isLocked() || ! $this->chat_started_at) {
            return 0;
        }

        return max(
            0,
            now()->diffInSeconds(
                $this->chat_started_at->copy()->addMinutes(15),
                false
            )
        );
    }
}