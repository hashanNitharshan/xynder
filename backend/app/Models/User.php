<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'original_name', 'email', 'password', 'phone',
        'role', 'balance', 'is_active', 'status', 'is_online',
        'last_seen_at', 'photo', 'address', 'country', 'state',
        'aadhaar', 'aadhaar_photo', 'card_number', 'bank_name',
        'branch', 'account_number', 'account_type', 'ifsc',
        'upi_name', 'upi_id', 'upi_qr', 'is_verified', 'wallet_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['photo_url', 'upi_qr_url', 'aadhaar_photo_url'];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_online'    => 'boolean',
        'last_seen_at' => 'datetime',
        'balance'      => 'decimal:2',
        'is_verified'  => 'boolean',
    ];

   

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->storageUrl($this->photo);
    }

    public function getUpiQrUrlAttribute(): ?string
    {
        return $this->storageUrl($this->upi_qr);
    }

    public function getAadhaarPhotoUrlAttribute(): ?string
    {
        return $this->storageUrl($this->aadhaar_photo);
    }



private function storageUrl(?string $path): ?string
{
    if (! $path || $path === '0' || trim($path) === '') {
        return null;
    }

    $path = str_replace('\\', '/', trim($path));

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return asset('storage/' . ltrim($path, '/'));
}
}