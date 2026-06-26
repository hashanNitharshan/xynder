<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'attachment_path',
        'attachment_url',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'attachment_type',
        'status',
        'delivered_at',
        'seen_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'seen_at'      => 'datetime',
    ];

    // -----------------------------------------------------------------------
    // FIX: Old chat messages in the database have attachment_url values like:
    //   https://wallet.bitxnow.com/api/storage/chat_attachments/filename.jpg
    //
    // This accessor rewrites them on-the-fly to the correct direct URL:
    //   https://wallet.bitxnow.com/storage/chat_attachments/filename.jpg
    //
    // New messages already get the correct URL from ChatController::storeAttachment(),
    // so this accessor is purely a backwards-compat fix for existing records —
    // no migration needed.
    // -----------------------------------------------------------------------
    public function getAttachmentUrlAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // Rewrite old incorrect /api/storage/ URLs to the direct /storage/ path
        if (str_contains($value, '/api/storage/')) {
            $appUrl = rtrim(config('app.url'), '/');
            $path   = preg_replace('#^.+/api/storage/#', '', $value);
            return $appUrl . '/storage/' . ltrim($path, '/');
        }

        return $value;
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}