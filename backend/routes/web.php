<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\WalletTransferController as WebWalletTransferController;
use App\Http\Controllers\WebChatController;
use App\Http\Controllers\WebProfileController;
use App\Http\Controllers\WebSettingsController;

use App\Http\Controllers\Client\RequestController as ClientRequestController;
use App\Http\Controllers\Merchant\RequestController as MerchantRequestController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletRequestController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\WalletTransferController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\WebPaymentDetailsController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'redirectByRole'])->name('dashboard');

    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard');

        Route::get('/requests', [ClientRequestController::class, 'index'])->name('requests');
        Route::post('/requests', [ClientRequestController::class, 'store'])->name('requests.store');

        Route::get('/transfers', [WebWalletTransferController::class, 'clientIndex'])->name('transfers');
        Route::post('/transfers/lookup', [WebWalletTransferController::class, 'lookup'])->name('transfers.lookup');
        Route::post('/transfers', [WebWalletTransferController::class, 'store'])->name('transfers.store');

        Route::get('/chats', [WebChatController::class, 'clientIndex'])->name('chats');
        Route::get('/chats/request/{walletRequest}', [WebChatController::class, 'showByRequest'])->name('chats.request');
        Route::get('/chats/transfer/{walletTransfer}', [WebChatController::class, 'showByTransfer'])->name('chats.transfer');
        Route::get('/chats/{conversation}', [WebChatController::class, 'show'])->name('chats.show');
        Route::post('/chats/{conversation}/send', [WebChatController::class, 'send'])->name('chats.send');

        Route::get('/history', [HistoryController::class, 'clientIndex'])->name('history');
        Route::get('/history/{sourceType}/{id}', [HistoryController::class, 'clientShow'])
            ->whereIn('sourceType', ['request', 'transfer'])
            ->whereNumber('id')
            ->name('history.show');

        Route::get('/profile', [WebProfileController::class, 'clientIndex'])->name('profile');
        Route::post('/profile', [WebProfileController::class, 'update'])->name('profile.update');

        Route::get('/settings', [WebSettingsController::class, 'clientIndex'])->name('settings');
        Route::post('/settings/profile', [WebSettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('/settings/password', [WebSettingsController::class, 'changePassword'])->name('settings.password');
        Route::post('/settings/support', [WebSettingsController::class, 'storeSupport'])->name('settings.support');

        Route::get('/payment-details', [WebPaymentDetailsController::class, 'clientIndex'])->name('payment-details');
        Route::post('/payment-details/bank', [WebPaymentDetailsController::class, 'storeBank'])->name('payment-details.bank.store');
        Route::put('/payment-details/bank/{bankAccount}', [WebPaymentDetailsController::class, 'updateBank'])->name('payment-details.bank.update');
        Route::delete('/payment-details/bank/{bankAccount}', [WebPaymentDetailsController::class, 'deleteBank'])->name('payment-details.bank.destroy');
        Route::post('/payment-details/bank/{bankAccount}/default', [WebPaymentDetailsController::class, 'setDefaultBank'])->name('payment-details.bank.default');
        Route::post('/payment-details/upi', [WebPaymentDetailsController::class, 'updateUpi'])->name('payment-details.upi');
        Route::post('/settings/bank', [WebSettingsController::class, 'storeBank'])->name('settings.bank.store');
        Route::put('/settings/bank/{bankAccount}', [WebSettingsController::class, 'updateBank'])->name('settings.bank.update');
        Route::delete('/settings/bank/{bankAccount}', [WebSettingsController::class, 'deleteBank'])->name('settings.bank.destroy');
        Route::post('/settings/bank/{bankAccount}/default', [WebSettingsController::class, 'setDefaultBank'])->name('settings.bank.default');
        Route::post('/settings/upi', [WebSettingsController::class, 'updateUpi'])->name('settings.upi');
    });

    Route::prefix('merchant')->name('merchant.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'merchant'])->name('dashboard');

        Route::get('/requests', [MerchantRequestController::class, 'index'])->name('requests');
        Route::post('/requests/{walletRequest}/approve', [MerchantRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{walletRequest}/reject', [MerchantRequestController::class, 'reject'])->name('requests.reject');

        Route::get('/transfers', [WebWalletTransferController::class, 'merchantIndex'])->name('transfers');
        Route::post('/transfers/lookup', [WebWalletTransferController::class, 'lookup'])->name('transfers.lookup');
        Route::post('/transfers', [WebWalletTransferController::class, 'store'])->name('transfers.store');

        Route::get('/chats', [WebChatController::class, 'merchantIndex'])->name('chats');
        Route::get('/chats/request/{walletRequest}', [WebChatController::class, 'showByRequest'])->name('chats.request');
        Route::get('/chats/transfer/{walletTransfer}', [WebChatController::class, 'showByTransfer'])->name('chats.transfer');
        Route::get('/chats/{conversation}', [WebChatController::class, 'show'])->name('chats.show');
        Route::post('/chats/{conversation}/send', [WebChatController::class, 'send'])->name('chats.send');
        Route::post('/requests/{walletRequest}/close', [MerchantRequestController::class, 'close'])->name('requests.close');

        Route::get('/history', [HistoryController::class, 'merchantIndex'])->name('history');
        Route::get('/history/{sourceType}/{id}', [HistoryController::class, 'merchantShow'])
            ->whereIn('sourceType', ['request', 'transfer'])
            ->whereNumber('id')
            ->name('history.show');

        Route::get('/profile', [WebProfileController::class, 'merchantIndex'])->name('profile');
        Route::post('/profile', [WebProfileController::class, 'update'])->name('profile.update');

        Route::get('/settings', [WebSettingsController::class, 'merchantIndex'])->name('settings');
        Route::post('/settings/profile', [WebSettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::post('/settings/password', [WebSettingsController::class, 'changePassword'])->name('settings.password');
        Route::post('/settings/support', [WebSettingsController::class, 'storeSupport'])->name('settings.support');

        Route::get('/payment-details', [WebPaymentDetailsController::class, 'merchantIndex'])->name('payment-details');
        Route::post('/payment-details/bank', [WebPaymentDetailsController::class, 'storeBank'])->name('payment-details.bank.store');
        Route::put('/payment-details/bank/{bankAccount}', [WebPaymentDetailsController::class, 'updateBank'])->name('payment-details.bank.update');
        Route::delete('/payment-details/bank/{bankAccount}', [WebPaymentDetailsController::class, 'deleteBank'])->name('payment-details.bank.destroy');
        Route::post('/payment-details/bank/{bankAccount}/default', [WebPaymentDetailsController::class, 'setDefaultBank'])->name('payment-details.bank.default');
        Route::post('/payment-details/upi', [WebPaymentDetailsController::class, 'updateUpi'])->name('payment-details.upi');
        Route::post('/settings/bank', [WebSettingsController::class, 'storeBank'])->name('settings.bank.store');
        Route::put('/settings/bank/{bankAccount}', [WebSettingsController::class, 'updateBank'])->name('settings.bank.update');
        Route::delete('/settings/bank/{bankAccount}', [WebSettingsController::class, 'deleteBank'])->name('settings.bank.destroy');
        Route::post('/settings/bank/{bankAccount}/default', [WebSettingsController::class, 'setDefaultBank'])->name('settings.bank.default');
        Route::post('/settings/upi', [WebSettingsController::class, 'updateUpi'])->name('settings.upi');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::resource('users', UserController::class);

        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/toggle-verification', [UserController::class, 'toggleVerification'])->name('users.toggle-verification');

        Route::get('wallet-requests', [WalletRequestController::class, 'index'])->name('wallet-requests.index');
        Route::post('wallet-requests/{walletRequest}/approve', [WalletRequestController::class, 'approve'])->name('wallet-requests.approve');
        Route::post('wallet-requests/{walletRequest}/reject', [WalletRequestController::class, 'reject'])->name('wallet-requests.reject');
        Route::post('wallet-requests/{walletRequest}/close', [WalletRequestController::class, 'close'])->name('wallet-requests.close');

        Route::get(
            'wallet-transfers',
            [WalletTransferController::class, 'index']
        )->name('wallet-transfers.index');

        Route::get(
            'wallet-transfers/{walletTransfer}',
            [WalletTransferController::class, 'show']
        )
            ->whereNumber('walletTransfer')
            ->name('wallet-transfers.show');

        Route::get('config', [ConfigController::class, 'index'])->name('config.index');
        Route::post('config', [ConfigController::class, 'store'])->name('config.store');

        Route::get('support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::post('support-tickets/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])->name('support-tickets.status');

        Route::get('wallet-transfers/{walletTransfer}/chat', [ChatController::class, 'openTransfer'])->name('wallet-transfers.chat');
        Route::get('wallet-requests/{walletRequest}/chat', [ChatController::class, 'openRequest'])->name('wallet-requests.chat');

        Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
        Route::get('chats/{conversation}', [ChatController::class, 'show'])->name('chats.show');
        Route::post('chats/{conversation}/send', [ChatController::class, 'send'])->name('chats.send');
        Route::post('users/{user}/bank', [UserController::class, 'storeBank'])->name('users.bank.store');
        Route::put('users/{user}/bank/{bankAccount}', [UserController::class, 'updateBank'])->name('users.bank.update');
        Route::delete('users/{user}/bank/{bankAccount}', [UserController::class, 'deleteBank'])->name('users.bank.destroy');
        Route::post('users/{user}/bank/{bankAccount}/default', [UserController::class, 'setDefaultBank'])->name('users.bank.default');
    });
});