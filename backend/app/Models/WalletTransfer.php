<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletTransfer extends Model
{
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'receiver_wallet_id',
        'transfer_type',
        'amount',
        'note',
        'transaction_no',
        'status',
        'reversed_at',
        'reversed_by',
        'reverse_reason',
    ];

    protected $attributes = [
        'status' => self::STATUS_COMPLETED,
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * The admin who reversed this transfer.
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'wallet_transfer_id');
    }

    public function isExternal(): bool
    {
        return $this->transfer_type === self::TYPE_EXTERNAL;
    }

    public function isInternal(): bool
    {
        return ! $this->isExternal();
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function canBeReversed(): bool
    {
        return ($this->status ?? self::STATUS_COMPLETED) === self::STATUS_COMPLETED;
    }

    protected static function booted(): void
    {
        static::created(function (WalletTransfer $transfer): void {
            if (! $transfer->transaction_no) {
                $transfer->transaction_no = 'TRA' . str_pad(
                    (string) $transfer->id,
                    9,
                    '0',
                    STR_PAD_LEFT
                );

                $transfer->saveQuietly();
            }
        });
    }
}