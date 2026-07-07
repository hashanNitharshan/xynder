<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'branch',
        'account_number',
        'account_type',
        'ifsc',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}