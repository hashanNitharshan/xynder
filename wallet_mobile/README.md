X/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── ChatController.php
│   │   │   │   │   ├── ConfigController.php
│   │   │   │   │   ├── SettingController.php
│   │   │   │   │   ├── SupportTicketController.php
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   ├── WalletRequestController.php
│   │   │   │   │   └── WalletTransferController.php
│   │   │   │   │
│   │   │   │   ├── Api/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ChatController.php
│   │   │   │   │   ├── ConfigController.php
│   │   │   │   │   ├── StorageController.php
│   │   │   │   │   └── SupportTicketController.php
│   │   │   │   │
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginController.php 
│   │   │   │   │   └── RegisterController.php              # ✅ NEW web signup controller
│   │   │   │   │
│   │   │   │   ├── Client/
│   │   │   │   │   └── RequestController.php
│   │   │   │   │
│   │   │   │   ├── Merchant/
│   │   │   │   │   └── RequestController.php
│   │   │   │   │
│   │   │   │   ├── Controller.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── HistoryController.php
│   │   │   │   ├── WalletTransferController.php
│   │   │   │   ├── WebChatController.php
│   │   │   │   └── WebSettingsController.php
│   │   │   │
│   │   │   └── Middleware/
│   │   │       └── AdminOnly.php
│   │   │
│   │   ├── Models/
│   │   │   ├── ChatMessage.php
│   │   │   ├── Conversation.php
│   │   │   ├── SupportTicket.php
│   │   │   ├── SystemConfig.php
│   │   │   ├── User.php
│   │   │   ├── WalletRequest.php
│   │   │   └── WalletTransfer.php
│   │   │
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   │
│   ├── bootstrap/
│   │   ├── app.php
│   │   └── providers.php
│   │
│   ├── config/
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── cache.php
│   │   ├── database.php
│   │   ├── filesystems.php
│   │   ├── logging.php
│   │   ├── mail.php
│   │   ├── queue.php
│   │   ├── sanctum.php
│   │   ├── services.php
│   │   └── session.php
│   │
│   ├── database/
│   │   ├── factories/
│   │   │   └── UserFactory.php
│   │   │
│   │   ├── migrations/
│   │   │   ├── 0001_01_01_000000_create_users_table.php
│   │   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   │   ├── 2026_05_12_114009_create_personal_access_tokens_table.php
│   │   │   ├── 2026_05_12_115117_add_role_phone_balance_to_users_table.php
│   │   │   ├── 2026_05_13_144140_add_full_profile_status_to_users_table.php
│   │   │   ├── 2026_05_13_144215_create_wallet_requests_table.php
│   │   │   ├── 2026_05_18_074548_add_advanced_profile_fields_to_users_table.php
│   │   │   ├── 2026_05_18_074715_add_profile_bank_upi_status_fields_to_users_table.php
│   │   │   ├── 2026_05_19_062755_add_is_active_to_users_table.php
│   │   │   ├── 2026_05_20_053642_create_system_configs_table.php
│   │   │   ├── 2026_05_20_053703_add_rate_fields_to_wallet_requests_table.php
│   │   │   ├── 2026_05_22_041236_add_merchant_and_payment_slip_to_wallet_requests_table.php
│   │   │   ├── 2026_05_25_062829_add_aadhaar_photo_to_users_table.php
│   │   │   ├── 2026_06_03_054603_create_support_tickets_table.php
│   │   │   ├── 2026_06_03_061152_add_is_verified_to_users_table.php
│   │   │   ├── 2026_06_04_034252_add_wallet_id_to_users_table.php
│   │   │   ├── 2026_06_04_034312_create_wallet_transfers_table.php
│   │   │   ├── 2026_06_09_153533_create_conversations_table.php
│   │   │   ├── 2026_06_10_023710_create_chat_messages_table.php
│   │   │   ├── 2026_06_10_111452_fix_conversations_chat_columns.php
│   │   │   ├── xxxx_xx_xx_xxxxxx_add_transaction_no_to_wallet_transfers_table.php
│   │   │   └── xxxx_xx_xx_xxxxxx_add_attachment_and_lock_columns_to_chat_tables.php
│   │   │
│   │   ├── seeders/
│   │   │   └── DatabaseSeeder.php
│   │   │
│   │   └── database.sqlite
│   │
│   ├── public/
│   │   ├── apk/
│   │   │   └── wallet-mobile.apk
│   │   │
│   │   ├── storage/
│   │   ├── index.php
│   │   └── robots.txt
│   │
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css
│   │   │
│   │   ├── js/
│   │   │   └── app.js
│   │   │
│   │   └── views/
│   │       ├── admin/
│   │       │   ├── chats/
│   │       │   │   ├── index.blade.php
│   │       │   │   └── show.blade.php
│   │       │   │
│   │       │   ├── config/
│   │       │   │   └── index.blade.php
│   │       │   │
│   │       │   ├── settings/
│   │       │   │   └── index.blade.php
│   │       │   │
│   │       │   ├── support_tickets/
│   │       │   │   └── index.blade.php
│   │       │   │
│   │       │   ├── users/
│   │       │   │   ├── form.blade.php
│   │       │   │   └── index.blade.php
│   │       │   │
│   │       │   ├── wallet_requests/
│   │       │   │   └── index.blade.php
│   │       │   │
│   │       │   └── wallet_transfers/
│   │       │       └── index.blade.php
│   │       │
│   │       ├── auth/
│   │       │   ├── login.blade.php                 # ✅ UPDATED signup link added
│   │       │   └── register.blade.php              # ✅ NEW web signup page
│   │       │
│   │       ├── client/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   └── settings.blade.php
│   │       │
│   │       ├── merchant/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   └── settings.blade.php
│   │       │
│   │       ├── shared/
│   │       │   ├── PS C:\Users\User\Desktop\Projects\X\backend\resources\views\shared> dir


    Directory: C:\Users\User\Desktop\Projects\X\backend\resources\views\shared


Mode                 LastWriteTime         Length Name                                                                                                                                     
----                 -------------         ------ ----                                                                                                                                     
-a----         6/23/2026   1:02 PM           3246 chats_index.blade.php                                                                                                                    
-a----         6/23/2026   1:02 PM           4691 chat_show.blade.php                                                                                                                      
-a----         6/23/2026   9:03 PM           9578 dark_dashboard.blade.php                                                                                                                 
-a----         6/23/2026   1:33 PM           4633 history.blade.php                                                                                                                        
-a----         6/23/2026   1:17 PM           6019 settings.blade.php                                                                                                                       
-a----         6/23/2026  12:48 PM           4739 transfers.blade.php                                                                                                                      

│   │       │
│   │       ├── dashboards/
│   │       │   ├── admin.blade.php
│   │       │   ├── client.blade.php
│   │       │   └── merchant.blade.php
│   │       │
│   │       ├── layouts/
│   │       │   └── admin.blade.php
│   │       │
│   │       └── welcome.blade.php
│   │
│   ├── routes/
│   │   ├── api.php
│   │   ├── console.php
│   │   └── web.php                              # ✅ UPDATED register routes added
│   │
│   ├── storage/
│   │   ├── app/
│   │   │   └── public/
│   │   │       ├── users/
│   │   │       │   ├── photos/
│   │   │       │   ├── aadhaar/
│   │   │       │   └── upi_qr/
│   │   │       │
│   │   │       ├── wallet/
│   │   │       │   └── slips/
│   │   │       │
│   │   │       └── chat_attachments/
│   │   │
│   │   ├── framework/
│   │   │   ├── cache/
│   │   │   ├── sessions/
│   │   │   ├── testing/
│   │   │   └── views/
│   │   │
│   │   └── logs/
│   │       └── laravel.log
│   │
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   │
│   ├── vendor/
│   ├── .editorconfig
│   ├── .env
│   ├── .env.example
│   ├── .gitattributes
│   ├── .gitignore
│   ├── artisan
│   ├── composer.json
│   ├── composer.lock
│   ├── package.json
│   ├── package-lock.json
│   ├── phpunit.xml
│   ├── README.md
│   └── vite.config.js
│
└── wallet_mobile/
    ├── assets/
    │   └── images/
    │       └── xynder_logo.png
    │
    ├── lib/
    │   ├── main.dart
    │   │
    │   ├── screens/
    │   │   ├── admin_dashboard.dart
    │   │   ├── auth_gate.dart
    │   │   ├── chat_screen.dart
    │   │   ├── chat_users_screen.dart
    │   │   ├── client_dashboard.dart
    │   │   ├── login_screen.dart
    │   │   ├── merchant_dashboard.dart
    │   │   ├── merchant_requests_screen.dart
    │   │   ├── profile_screen.dart
    │   │   ├── register_screen.dart               # ✅ Flutter signup already working
    │   │   ├── request_screen.dart
    │   │   ├── transaction_detail_screen.dart
    │   │   └── wallet_transfer_screen.dart
    │   │
    │   ├── services/
    │   │   └── api_service.dart                   # ✅ Flutter uses /api/register
    │   │
    │   ├── utils/
    │   │   ├── tv_iframe_registry.dart
    │   │   └── tv_iframe_registry_stub.dart
    │   │
    │   └── widgets/
    │       ├── app_drawer.dart
    │       ├── bottom_nav.dart
    │       ├── dashboard_layout.dart
    │       ├── pinwheel_loader.dart
    │       ├── top_bar.dart
    │       ├── trading_widgets.dart
    │       ├── tv_ticker.dart
    │       ├── tv_widgets.dart
    │       ├── tv_widgets_stub.dart
    │       └── tv_widgets_web.dart
    │
    ├── android/
    │   ├── app/
    │   ├── build.gradle
    │   └── settings.gradle
    │
    ├── ios/
    │   ├── Runner/
    │   ├── Runner.xcodeproj/
    │   └── Runner.xcworkspace/
    │
    ├── linux/
    │   ├── flutter/
    │   ├── runner/
    │   └── CMakeLists.txt
    │
    ├── macos/
    │   ├── Flutter/
    │   ├── Runner/
    │   ├── Runner.xcodeproj/
    │   └── Runner.xcworkspace/
    │
    ├── test/
    │   └── widget_test.dart
    │
    ├── web/
    │   ├── favicon.png
    │   ├── icons/
    │   ├── index.html
    │   └── manifest.json
    │
    ├── windows/
    │   ├── flutter/
    │   ├── runner/
    │   └── CMakeLists.txt
    │
    ├── .flutter-plugins-dependencies
    ├── .gitignore
    ├── pubspec.yaml
    └── README.md
    
flutter run -d chrome

