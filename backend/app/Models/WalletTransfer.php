<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransfer extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'receiver_wallet_id',
        'amount',
        'note',
         'transaction_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'wallet_transfer_id');
    }


    protected static function booted(): void
{
    static::created(function ($transfer) {
        if (!$transfer->transaction_no) {
            $transfer->transaction_no = 'TRA' . str_pad($transfer->id, 9, '0', STR_PAD_LEFT);
            $transfer->saveQuietly();
        }
    });
}
}