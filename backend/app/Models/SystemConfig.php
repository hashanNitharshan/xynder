<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = [
        'usd_rate',
        'inr_rate',
        'xynder_fee',
        'network_fee',
        'effective_date',
    ];

    protected $casts = [
        'usd_rate' => 'decimal:4',
        'inr_rate' => 'decimal:4',
        'xynder_fee' => 'decimal:2',
        'network_fee' => 'decimal:2',
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
                'usd_rate' => 1,
                'inr_rate' => 1,
                'xynder_fee' => 0,
                'network_fee' => 0,
                'effective_date' => today(),
            ]);
    }
}