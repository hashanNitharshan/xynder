<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'original_name',
        'email',
        'password',
        'phone',
        'role',
        'balance',
        'is_active',
        'status',
        'is_online',
        'last_seen_at',
        'photo',
        'address',
        'country',
        'state',
        'aadhaar',
        'aadhaar_photo',
        'card_number',
        'bank_name',
        'branch',
        'account_number',
        'account_type',
        'ifsc',
        'upi_name',
        'upi_id',
        'upi_qr',
        'is_verified',
        'wallet_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'photo_url',
        'upi_qr_url',
        'aadhaar_photo_url',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_online'    => 'boolean',
        'is_verified'  => 'boolean',
        'last_seen_at' => 'datetime',
        'balance'      => 'decimal:2',
    ];

    /**
     * Automatically generate the wallet ID whenever a user is created.
     *
     * This works for:
     * - Website registration
     * - Mobile registration
     * - Admin-created clients
     * - Admin-created merchants
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->wallet_id)) {
                $user->wallet_id = self::generateWalletId();
            }
        });
    }

    /**
     * Generate a unique 25-character wallet ID.
     *
     * Example:
     * XyW9F3dKZ7jNGG52Q8VB4m1TR
     */
    public static function generateWalletId(): string
    {
        do {
            $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $lowercase = 'abcdefghijklmnopqrstuvwxyz';
            $numbers   = '0123456789';

            $allCharacters = $uppercase . $lowercase . $numbers;

            /*
             * Prefix is 3 characters.
             */
            $prefix = 'XyW';

            /*
             * Guarantee that the random part contains:
             * - At least one uppercase letter
             * - At least one lowercase letter
             * - At least one number
             */
            $characters = [
                $uppercase[random_int(0, strlen($uppercase) - 1)],
                $lowercase[random_int(0, strlen($lowercase) - 1)],
                $numbers[random_int(0, strlen($numbers) - 1)],
            ];

            /*
             * Prefix = 3 characters
             * Random section = 22 characters
             * Total = 25 characters
             *
             * Already added 3 required characters,
             * so add another 19 characters.
             */
            for ($i = 0; $i < 19; $i++) {
                $characters[] = $allCharacters[
                    random_int(0, strlen($allCharacters) - 1)
                ];
            }

            /*
             * Securely shuffle the random characters.
             */
            for ($i = count($characters) - 1; $i > 0; $i--) {
                $randomIndex = random_int(0, $i);

                $temporary = $characters[$i];
                $characters[$i] = $characters[$randomIndex];
                $characters[$randomIndex] = $temporary;
            }

            $walletId = $prefix . implode('', $characters);
        } while (
            self::where('wallet_id', $walletId)->exists()
        );

        return $walletId;
    }

    /**
     * User bank accounts.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(
            UserBankAccount::class,
            'user_id'
        );
    }

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
        if (
            !$path ||
            $path === '0' ||
            trim($path) === ''
        ) {
            return null;
        }

        $path = str_replace(
            '\\',
            '/',
            trim($path)
        );

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://')
        ) {
            return str_replace(
                '/api/storage/',
                '/storage/',
                $path
            );
        }

        $path = preg_replace(
            '#^/?api/storage/#',
            '',
            $path
        );

        $path = preg_replace(
            '#^/?storage/#',
            '',
            $path
        );

        return url(
            '/storage/' . ltrim($path, '/')
        );
    }
}