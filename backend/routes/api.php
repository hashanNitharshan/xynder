<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\ChatController;

Route::options('/{any}', function () {
    return response()->json([], 204);
})->where('any', '.*');

Route::get('/version', function () {
    return response()->json([
        'success'      => true,
        'version'      => '1.1.5',
        'apk_url'      => 'https://wallet.bitxnow.com/apk/wallet-mobile.apk',
        'force_update' => false,
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/storage/{path}', [StorageController::class, 'show'])
    ->where('path', '.*');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/ping', [AuthController::class, 'ping']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/config', [ConfigController::class, 'current']);
    Route::get('/merchants', [AuthController::class, 'merchants']);

    Route::get('/requests', [AuthController::class, 'myRequests']);
    Route::post('/requests', [AuthController::class, 'createRequest']);

    Route::match(
        ['post', 'put', 'patch'],
        '/requests/{walletRequest}/approve',
        [AuthController::class, 'merchantApproveRequest']
    );

    Route::match(
        ['post', 'put', 'patch'],
        '/requests/{walletRequest}/reject',
        [AuthController::class, 'merchantRejectRequest']
    );

    // Close a pending request — allowed for the owning client OR the
    // assigned merchant. Role/ownership checks happen in the controller.
    Route::match(
        ['post', 'put', 'patch'],
        '/requests/{walletRequest}/close',
        [AuthController::class, 'merchantCloseRequest']
    );

    Route::get('/support-tickets', [SupportTicketController::class, 'index']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);

    Route::post('/wallet/lookup', [AuthController::class, 'walletLookup']);
    Route::post('/wallet/transfer', [AuthController::class, 'walletTransfer']);
    Route::get('/wallet/transfers', [AuthController::class, 'walletTransfers']);

    Route::get('/payment-methods', [AuthController::class, 'paymentMethods']);
    Route::post('/bank-accounts', [AuthController::class, 'storeBankAccount']);
    Route::put('/bank-accounts/{bankAccount}', [AuthController::class, 'updateBankAccount']);
    Route::delete('/bank-accounts/{bankAccount}', [AuthController::class, 'deleteBankAccount']);
    Route::put('/payment-upi', [AuthController::class, 'updateUpi']);

    Route::get('/chat/conversations', [ChatController::class, 'conversations']);
    Route::get('/chat/transfer/{transferId}/messages', [ChatController::class, 'transferMessages']);
    Route::post('/chat/transfer/{transferId}/messages', [ChatController::class, 'sendTransferMessage']);
    Route::get('/chat/request/{requestId}/messages', [ChatController::class, 'requestMessages']);
    Route::post('/chat/request/{requestId}/messages', [ChatController::class, 'sendRequestMessage']);
    Route::post('/chat/delivered', [ChatController::class, 'markDelivered']);
});