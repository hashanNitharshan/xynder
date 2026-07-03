<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\User;
use App\Models\WalletRequest;
use App\Models\WalletTransfer;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with([
            'userOne:id,name,email,role,photo,is_online,last_seen_at',
            'userTwo:id,name,email,role,photo,is_online,last_seen_at',
            'walletTransfer',
            'walletRequest',
        ])
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($conversation) use ($userId) {
                $conversation = $this->syncRequestChatLock($conversation);

                $other = $conversation->user_one_id == $userId
                    ? $conversation->userTwo
                    : $conversation->userOne;

                if (! $other) {
                    return null;
                }

                $lastMessage = ChatMessage::where('conversation_id', $conversation->id)
                    ->latest()
                    ->first();

                $unreadCount = ChatMessage::where('conversation_id', $conversation->id)
                    ->where('receiver_id', $userId)
                    ->whereIn('status', ['sent', 'delivered'])
                    ->count();

                $isTransfer = ! is_null($conversation->wallet_transfer_id);
                $isLocked   = $conversation->isLocked();

                return [
                    'conversation_id' => $conversation->id,
                    'chat_type'       => $isTransfer ? 'transfer' : 'request',
                    'chat_id'         => $isTransfer
                        ? (int) $conversation->wallet_transfer_id
                        : (int) $conversation->wallet_request_id,

                    'is_locked'        => $isLocked,
                    'can_open'         => ! $isLocked,
                    'chat_started_at'  => $conversation->chat_started_at,
                    'locked_at'        => $conversation->locked_at,
                    'lock_reason'      => $conversation->lock_reason,
                    'remaining_seconds' => $conversation->remainingSeconds(),

                    'other_user' => [
                        'id'           => $other->id,
                        'name'         => $other->name,
                        'email'        => $other->email,
                        'role'         => $other->role,
                        'photo'        => $other->photo,
                        'photo_url'    => $other->photo_url,
                        'is_online'    => $other->is_online,
                        'last_seen_at' => $other->last_seen_at,
                    ],

                    'last_message' => $isLocked
                        ? '🔒 Chat locked'
                        : ($lastMessage?->message ?: ($lastMessage?->attachment_name ? '📎 ' . $lastMessage->attachment_name : null)),

                    'last_message_at' => $lastMessage?->created_at,
                    'unread_count'    => $unreadCount,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'success'       => true,
            'conversations' => $conversations,
        ]);
    }

    public function transferMessages(Request $request, $transferId)
    {
        $conversation = $this->getOrCreateTransferConversation($request, $transferId);
        $conversation->lockIfExpired();

        if ($conversation->isLocked()) {
            return $this->lockedResponse($conversation);
        }

        $this->markSeen($conversation->id, $request->user()->id);

        return response()->json([
            'success'           => true,
            'is_locked'         => false,
            'remaining_seconds' => $conversation->remainingSeconds(),
            'conversation'      => $conversation,
            'other_user'        => $this->userData($this->otherUser($conversation, $request->user()->id)),
            'messages'          => $this->messages($conversation->id),
        ]);
    }

    public function sendTransferMessage(Request $request, $transferId)
    {
        return $this->sendMessage($request, $this->getOrCreateTransferConversation($request, $transferId));
    }

    public function requestMessages(Request $request, $requestId)
    {
        $conversation = $this->getOrCreateRequestConversation($request, $requestId);
        $conversation = $this->syncRequestChatLock($conversation);

        if ($conversation->isLocked()) {
            return $this->lockedResponse($conversation);
        }

        $this->markSeen($conversation->id, $request->user()->id);

        return response()->json([
            'success'            => true,
            'is_locked'          => false,
            'can_open'           => true,
            'remaining_seconds'  => $conversation->remainingSeconds(),
            'conversation'       => $conversation,
            'other_user'         => $this->userData($this->otherUser($conversation, $request->user()->id)),
            'messages'           => $this->messages($conversation->id),
        ]);
    }

    public function sendRequestMessage(Request $request, $requestId)
    {
        $conversation = $this->getOrCreateRequestConversation($request, $requestId);
        $conversation = $this->syncRequestChatLock($conversation);

        return $this->sendMessage($request, $conversation);
    }

    /**
     * Auto-close a request that has been sitting "pending" for 10+ minutes,
     * then let the Conversation model apply the lock based on the (possibly
     * just-updated) request status. This is the ONLY place the 10-minute
     * pending timeout is enforced, and it runs on every chat fetch/send —
     * so it self-heals regardless of which controller approved/rejected/
     * closed the request.
     */
    private function syncRequestChatLock(Conversation $conversation): Conversation
    {
        if ($conversation->wallet_request_id) {
            $walletRequest = WalletRequest::find($conversation->wallet_request_id);

            if ($walletRequest && strtolower((string) $walletRequest->status) === 'pending'
                && $walletRequest->created_at
                && now()->diffInMinutes($walletRequest->created_at) >= 10) {
                $walletRequest->update(['status' => 'closed']);
            }
        }

        $conversation->lockIfExpired();

        return $conversation->fresh();
    }

    private function sendMessage(Request $request, Conversation $conversation)
    {
        $conversation->lockIfExpired();

        if ($conversation->isLocked()) {
            return $this->lockedResponse($conversation);
        }

        $data = $request->validate([
            'message'    => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        if (empty($data['message']) && ! $request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Message or attachment is required.',
            ], 422);
        }

        if (! $conversation->chat_started_at) {
            $conversation->update([
                'chat_started_at' => now(),
            ]);
        }

        $receiver = $this->otherUser($conversation, $request->user()->id);

        $message = ChatMessage::create(array_merge([
            'conversation_id' => $conversation->id,
            'sender_id'       => $request->user()->id,
            'receiver_id'     => $receiver->id,
            'message'         => $data['message'] ?? '',
            'status'          => 'sent',
        ], $this->storeAttachment($request)));

        $conversation->touch();

        return response()->json([
            'success'           => true,
            'is_locked'         => false,
            'remaining_seconds' => $conversation->remainingSeconds(),
            'message'           => $message->load([
                'sender:id,name,email,role,photo',
                'receiver:id,name,email,role,photo',
            ]),
        ]);
    }

    public function markDelivered(Request $request)
    {
        ChatMessage::where('receiver_id', $request->user()->id)
            ->where('status', 'sent')
            ->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    private function messages($conversationId)
    {
        return ChatMessage::with([
            'sender:id,name,email,role,photo',
            'receiver:id,name,email,role,photo',
        ])
            ->where('conversation_id', $conversationId)
            ->oldest()
            ->get();
    }

    private function getOrCreateTransferConversation(Request $request, $transferId)
    {
        $transfer = WalletTransfer::findOrFail($transferId);
        $userId   = $request->user()->id;

        if ($transfer->sender_id != $userId && $transfer->receiver_id != $userId) {
            abort(403, 'Unauthorized chat.');
        }

        return Conversation::firstOrCreate(
            ['wallet_transfer_id' => $transfer->id],
            [
                'user_one_id'       => $transfer->sender_id,
                'user_two_id'       => $transfer->receiver_id,
                'wallet_request_id' => null,
            ]
        );
    }

    private function getOrCreateRequestConversation(Request $request, $requestId)
    {
        $walletRequest = WalletRequest::findOrFail($requestId);
        $userId        = $request->user()->id;

        if ($walletRequest->user_id != $userId && $walletRequest->merchant_id != $userId) {
            abort(403, 'Unauthorized chat.');
        }

        if (! $walletRequest->merchant_id) {
            abort(422, 'This request has no merchant assigned yet.');
        }

        return Conversation::firstOrCreate(
            ['wallet_request_id' => $walletRequest->id],
            [
                'user_one_id'          => $walletRequest->user_id,
                'user_two_id'          => $walletRequest->merchant_id,
                'wallet_transfer_id'   => null,
            ]
        );
    }

    private function otherUser(Conversation $conversation, $userId)
    {
        $otherId = $conversation->user_one_id == $userId
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        return User::select('id', 'name', 'email', 'role', 'photo', 'is_online', 'last_seen_at')
            ->findOrFail($otherId);
    }

    private function markSeen($conversationId, $userId)
    {
        ChatMessage::where('conversation_id', $conversationId)
            ->where('receiver_id', $userId)
            ->whereIn('status', ['sent', 'delivered'])
            ->update([
                'status'  => 'seen',
                'seen_at' => now(),
            ]);
    }

    private function storeAttachment(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');

        if (! $file->isValid()) {
            throw new \Exception('Uploaded file is not valid.');
        }

        $path = $file->store('chat_attachments', 'public');
        $mime = $file->getMimeType() ?: 'application/octet-stream';

        return [
            'attachment_path' => $path,
            'attachment_url'  => asset('storage/' . $path),
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime' => $mime,
            'attachment_size' => $file->getSize(),
            'attachment_type' => str_starts_with($mime, 'image/') ? 'image' : 'file',
        ];
    }

    private function userData($user): array
    {
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
            'photo'        => $user->photo,
            'photo_url'    => $user->photo_url,
            'is_online'    => $user->is_online,
            'last_seen_at' => $user->last_seen_at,
        ];
    }

    private function lockedResponse(Conversation $conversation)
    {
        return response()->json([
            'success'            => false,
            'is_locked'          => true,
            'can_open'           => false,
            'message'            => 'This chat is closed / locked.',
            'locked_at'          => $conversation->locked_at,
            'lock_reason'        => $conversation->lock_reason,
            'remaining_seconds'  => 0,
            'conversation'       => $conversation,
            'messages'           => $this->messages($conversation->id),
        ], 423);
    }
}