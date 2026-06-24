<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletRequest extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'type',
        'amount',
        'usd_rate',
        'inr_rate',
        'xynder_fee',
        'network_fee',
        'converted_amount',
        'fee',
        'total_amount',
        'status',
        'note',
        'payment_slip',
        'approved_by',
        'approved_at',
         'transaction_no',
    ];

    protected $appends = ['payment_slip_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'wallet_request_id');
    }

    public function getPaymentSlipUrlAttribute(): ?string
    {
        return $this->payment_slip ? url('/api/storage/' . $this->payment_slip) : null;
    }
    protected static function booted(): void
{
    static::created(function ($request) {
        if (!$request->transaction_no) {
            $request->transaction_no = 'TNS' . str_pad($request->id, 9, '0', STR_PAD_LEFT);
            $request->saveQuietly();
        }
    });
}
}