<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletTransfer extends Model
{
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'receiver_wallet_id',
        'transfer_type',
        'amount',
        'note',
        'transaction_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
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
