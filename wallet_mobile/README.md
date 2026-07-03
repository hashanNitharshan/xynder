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
│   │   │   │   ├── Api/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ChatController.php
│   │   │   │   │   ├── ConfigController.php
│   │   │   │   │   ├── StorageController.php
│   │   │   │   │   └── SupportTicketController.php
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginController.php
│   │   │   │   │   └── RegisterController.php
│   │   │   │   ├── Client/
│   │   │   │   │   └── RequestController.php
│   │   │   │   ├── Merchant/
│   │   │   │   │   └── RequestController.php
│   │   │   │   ├── Controller.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── HistoryController.php
│   │   │   │   ├── WalletTransferController.php
│   │   │   │   ├── WebChatController.php
│   │   │   │   ├── WebProfileController.php
│   │   │   │   └── WebSettingsController.php
│   │   │   └── Middleware/
│   │   │       ├── AdminOnly.php
│   │   │       └── HandleCors.php
│   │   ├── Models/
│   │   │   ├── ChatMessage.php
│   │   │   ├── Conversation.php
│   │   │   ├── SupportTicket.php
│   │   │   ├── SystemConfig.php
│   │   │   ├── User.php
│   │   │   ├── WalletRequest.php
│   │   │   └── WalletTransfer.php
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   ├── bootstrap/
│   │   ├── app.php
│   │   └── providers.php
│   ├── config/
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── cache.php
│   │   ├── cors.php
│   │   ├── database.php
│   │   ├── filesystems.php
│   │   ├── logging.php
│   │   ├── mail.php
│   │   ├── queue.php
│   │   ├── sanctum.php
│   │   ├── services.php
│   │   └── session.php
│   ├── database/
│   │   ├── factories/
│   │   │   └── UserFactory.php
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
│   │   │   ├── 2026_06_11_124406_add_attachments_to_chat_messages_table.php
│   │   │   ├── 2026_06_16_074000_add_chat_lock_fields_to_conversations_table.php
│   │   │   ├── 2026_06_18_052400_add_transaction_no_to_wallet_requests_and_transfers.php
│   │   │   ├── 2026_06_23_071904_add_transaction_no_to_wallet_transfers_table.php
│   │   │   ├── 2026_06_23_073254_add_attachment_and_lock_columns_to_chat_tables.php
│   │   │   └── 2026_07_01_141804_add_closed_status_to_wallet_requests_table.php
│   │   ├── seeders/
│   │   │   └── DatabaseSeeder.php
│   │   └── database.sqlite
│   ├── public/
│   │   ├── apk/
│   │   │   └── wallet-mobile.apk
│   │   ├── images/
│   │   │   └── bitxnow_logo.jpeg
│   │   ├── storage/
│   │   ├── favicon.ico
│   │   ├── index.php
│   │   └── robots.txt
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css
│   │   ├── js/
│   │   │   └── app.js
│   │   └── views/
│   │       ├── admin/
│   │       │   ├── chats/
│   │       │   │   ├── index.blade.php
│   │       │   │   └── show.blade.php
│   │       │   ├── config/
│   │       │   │   └── index.blade.php
│   │       │   ├── settings/
│   │       │   │   └── index.blade.php
│   │       │   ├── support_tickets/
│   │       │   │   └── index.blade.php
│   │       │   ├── users/
│   │       │   │   ├── form.blade.php
│   │       │   │   └── index.blade.php
│   │       │   ├── wallet_requests/
│   │       │   │   └── index.blade.php
│   │       │   └── wallet_transfers/
│   │       │       └── index.blade.php
│   │       ├── auth/
│   │       │   ├── login.blade.php
│   │       │   └── register.blade.php
│   │       ├── client/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── profile.blade.php
│   │       │   └── settings.blade.php
│   │       ├── merchant/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── profile.blade.php
│   │       │   └── settings.blade.php
│   │       ├── shared/
│   │       │   ├── chats_index.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── dark_dashboard.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── profile.blade.php
│   │       │   ├── settings.blade.php
│   │       │   ├── transaction_detail.blade.php
│   │       │   └── transfers.blade.php
│   │       ├── dashboards/
│   │       │   ├── admin.blade.php
│   │       │   ├── client.blade.php
│   │       │   └── merchant.blade.php
│   │       ├── layouts/
│   │       │   └── admin.blade.php
│   │       └── welcome.blade.php
│   ├── routes/
│   │   ├── api.php
│   │   ├── console.php
│   │   └── web.php
│   ├── storage/
│   │   ├── app/
│   │   │   └── public/
│   │   │       ├── users/
│   │   │       │   ├── photos/
│   │   │       │   ├── aadhaar/
│   │   │       │   └── upi_qr/
│   │   │       ├── wallet/
│   │   │       │   └── slips/
│   │   │       └── chat_attachments/
│   │   ├── framework/
│   │   │   ├── cache/
│   │   │   ├── sessions/
│   │   │   ├── testing/
│   │   │   └── views/
│   │   └── logs/
│   │       └── laravel.log
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
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
├── cloudpanel-nginx/
│   └── wallet.bitxnow.com.conf
└── wallet_mobile/
    ├── .dart_tool/
    ├── .idea/
    ├── .vscode/
    │   └── launch.json
    ├── android/
    │   ├── .gradle/
    │   ├── app/
    │   │   ├── src/
    │   │   │   ├── debug/
    │   │   │   └── main/
    │   │   │       ├── java/
    │   │   │       ├── kotlin/
    │   │   │       ├── res/
    │   │   │       │   ├── drawable/
    │   │   │       │   ├── drawable-hdpi/
    │   │   │       │   ├── drawable-mdpi/
    │   │   │       │   ├── drawable-v21/
    │   │   │       │   ├── drawable-xhdpi/
    │   │   │       │   ├── drawable-xxhdpi/
    │   │   │       │   ├── drawable-xxxhdpi/
    │   │   │       │   ├── mipmap-anydpi-v26/
    │   │   │       │   ├── mipmap-hdpi/
    │   │   │       │   ├── mipmap-mdpi/
    │   │   │       │   ├── mipmap-xhdpi/
    │   │   │       │   ├── mipmap-xxhdpi/
    │   │   │       │   ├── mipmap-xxxhdpi/
    │   │   │       │   ├── values/
    │   │   │       │   ├── values-night/
    │   │   │       │   └── xml/
    │   │   │       └── AndroidManifest.xml
    │   │   └── build.gradle
    │   ├── gradle/
    │   │   └── wrapper/
    │   │       └── gradle-wrapper.properties
    │   ├── build.gradle
    │   ├── gradle.properties
    │   ├── local.properties
    │   └── settings.gradle
    ├── assets/
    │   └── images/
    │       ├── bitxnow_logo.jpeg
    │       ├── bitxnow_logo2.jpeg
    │       ├── bitxnow_logo4.jpeg
    │       ├── bitxnow_logo5.jpeg
    │       ├── compressed-original.webp
    │       └── xynder_logo.jpg
    ├── build/
    ├── ios/
    │   ├── Flutter/
    │   ├── Runner/
    │   ├── Runner.xcodeproj/
    │   └── Runner.xcworkspace/
    ├── lib/
    │   ├── main.dart
    │   ├── screens/
    │   │   ├── admin_dashboard.dart
    │   │   ├── auth_gate.dart
    │   │   ├── chat_detail_screen.dart
    │   │   ├── chat_list_screen.dart
    │   │   ├── chat_screen.dart
    │   │   ├── chat_users_screen.dart
    │   │   ├── client_dashboard.dart
    │   │   ├── history_screen.dart
    │   │   ├── login_screen.dart
    │   │   ├── merchant_dashboard.dart
    │   │   ├── merchant_requests_screen.dart
    │   │   ├── profile_screen.dart
    │   │   ├── register_screen.dart
    │   │   ├── request_screen.dart
    │   │   ├── settings_screen.dart
    │   │   ├── transaction_detail_screen.dart
    │   │   ├── update_screen.dart
    │   │   └── wallet_transfer_screen.dart
    │   ├── services/
    │   │   ├── api_service.dart
    │   │   └── update_service.dart
    │   ├── utils/
    │   │   ├── page_transitions.dart
    │   │   ├── tv_iframe_registry.dart
    │   │   └── tv_iframe_registry_stub.dart
    │   └── widgets/
    │       ├── animated_page.dart
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
    ├── linux/
    │   ├── flutter/
    │   ├── runner/
    │   └── CMakeLists.txt
    ├── macos/
    │   ├── Flutter/
    │   ├── Runner/
    │   ├── Runner.xcodeproj/
    │   └── Runner.xcworkspace/
    ├── test/
    │   └── widget_test.dart
    ├── web/
    │   ├── favicon.png
    │   ├── icons/
    │   ├── index.html
    │   └── manifest.json
    ├── windows/
    │   ├── flutter/
    │   ├── runner/
    │   └── CMakeLists.txt
    ├── .flutter-plugins-dependencies
    ├── .gitignore
    ├── pubspec.yaml
    ├── README.md
    └── wallet_mobile_android.iml

flutter run -d chrome

