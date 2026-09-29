<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = [
        'usd_rate',
        'inr_rate',
        'buy_inr_rate',
        'sell_inr_rate',
        'xynder_fee',
        'network_fee',
        'effective_date',
    ];

    protected $casts = [
        'usd_rate'       => 'decimal:4',
        'inr_rate'       => 'decimal:4',
        'buy_inr_rate'   => 'decimal:4',
        'sell_inr_rate'  => 'decimal:4',
        'xynder_fee'     => 'decimal:2',
        'network_fee'    => 'decimal:2',
        'effective_date' => 'date',
    ];

    public static function current(): self
    {
        $config = self::whereDate('effective_date', '<=', today())
            ->latest('effective_date')
            ->latest('id')
            ->first();

        if ($config) {
            return $config;
        }

        return self::latest('effective_date')->latest('id')->first()
            ?? self::create([
                'usd_rate'       => 1,
                'inr_rate'       => 1,
                'buy_inr_rate'   => 1,
                'sell_inr_rate'  => 1,
                'xynder_fee'     => 0,
                'network_fee'    => 0,
                'effective_date' => today(),
            ]);
    }

    /**
     * INR per 1 USD when the client BUYS USD (deposit).
     * Falls back to the old single inr_rate if the new value is empty.
     */
    public function buyRate(): float
    {
        $rate = (float) ($this->buy_inr_rate ?? 0);

        return $rate > 0 ? $rate : (float) $this->inr_rate;
    }

    /**
     * INR per 1 USD when the client SELLS USD (withdrawal).
     * Falls back to the old single inr_rate if the new value is empty.
     */
    public function sellRate(): float
    {
        $rate = (float) ($this->sell_inr_rate ?? 0);

        return $rate > 0 ? $rate : (float) $this->inr_rate;
    }

    /**
     * Correct INR rate for a request type.
     * deposit    = Buy USD
     * withdrawal = Sell USD
     */
    public function rateFor(string $type): float
    {
        return $type === 'deposit'
            ? $this->buyRate()
            : $this->sellRate();
    }
}
