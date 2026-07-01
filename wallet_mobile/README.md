X/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── ChatController.php                  # UPDATED - attachment URL uses /storage
│   │   │   │   │   ├── ConfigController.php
│   │   │   │   │   ├── SettingController.php
│   │   │   │   │   ├── SupportTicketController.php
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   ├── WalletRequestController.php
│   │   │   │   │   └── WalletTransferController.php
│   │   │   │   │
│   │   │   │   ├── Api/
│   │   │   │   │   ├── AuthController.php                   # UPDATED - mobile profile upload support
│   │   │   │   │   ├── ChatController.php                   # UPDATED - attachment_url uses /storage
│   │   │   │   │   ├── ConfigController.php
│   │   │   │   │   ├── StorageController.php                # OLD /api/storage fallback
│   │   │   │   │   └── SupportTicketController.php
│   │   │   │   │
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── LoginController.php
│   │   │   │   │   └── RegisterController.php
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
│   │   │   │   ├── WebProfileController.php                 # NEW / UPDATED - client + merchant profile upload
│   │   │   │   └── WebSettingsController.php
│   │   │   │
│   │   │   └── Middleware/
│   │   │       ├── AdminOnly.php
│   │   │       └── HandleCors.php / Laravel HandleCors       # ADDED in bootstrap/app.php
│   │   │
│   │   ├── Models/
│   │   │   ├── ChatMessage.php                              # UPDATED - /api/storage converted to /storage
│   │   │   ├── Conversation.php
│   │   │   ├── SupportTicket.php
│   │   │   ├── SystemConfig.php
│   │   │   ├── User.php                                     # UPDATED - photo_url, aadhaar_photo_url, upi_qr_url
│   │   │   ├── WalletRequest.php                            # UPDATED - payment_slip_url uses /storage
│   │   │   └── WalletTransfer.php
│   │   │
│   │   └── Providers/
│   │       └── AppServiceProvider.php
│   │
│   ├── bootstrap/
│   │   ├── app.php                                          # UPDATED - CORS middleware added
│   │   └── providers.php
│   │
│   ├── config/
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── cache.php
│   │   ├── cors.php                                         # UPDATED - allowed_origins ['*']
│   │   ├── database.php
│   │   ├── filesystems.php                                  # UPDATED - public disk + storage link
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
│   │   │   └── wallet-mobile.apk                            # RELEASE APK hosted here
│   │   │
│   │   ├── storage/                                         # SYMLINK to storage/app/public
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
│   │       │   │   └── index.blade.php                       # UPDATED slip image URL
│   │       │   └── wallet_transfers/
│   │       │       └── index.blade.php
│   │       │
│   │       ├── auth/
│   │       │   ├── login.blade.php
│   │       │   └── register.blade.php
│   │       │
│   │       ├── client/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── profile.blade.php                         # NEW / UPDATED profile page
│   │       │   └── settings.blade.php                        # UPDATED profile image URL
│   │       │
│   │       ├── merchant/
│   │       │   ├── dashboard.blade.php
│   │       │   ├── requests.blade.php
│   │       │   ├── transfers.blade.php
│   │       │   ├── chats.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── profile.blade.php                         # NEW / UPDATED profile page
│   │       │   └── settings.blade.php                        # UPDATED profile image URL
│   │       │
│   │       ├── shared/
│   │       │   ├── chats_index.blade.php
│   │       │   ├── chat_show.blade.php
│   │       │   ├── dark_dashboard.blade.php
│   │       │   ├── history.blade.php
│   │       │   ├── settings.blade.php                        # UPDATED profile image URL
│   │       │   └── transfers.blade.php
│   │       │
│   │       ├── dashboards/
│   │       │   ├── admin.blade.php
│   │       │   ├── client.blade.php
│   │       │   └── merchant.blade.php
│   │       │
│   │       ├── layouts/
│   │       │   └── admin.blade.php                           # UPDATED sidebar/topbar profile photo URL
│   │       │
│   │       └── welcome.blade.php
│   │
│   ├── routes/
│   │   ├── api.php                                           # UPDATED OPTIONS + /version + /storage route
│   │   ├── console.php
│   │   └── web.php                                           # UPDATED client/merchant profile routes
│   │
│   ├── storage/
│   │   ├── app/
│   │   │   └── public/
│   │   │       ├── users/
│   │   │       │   ├── photos/                               # profile photos saved here
│   │   │       │   ├── aadhaar/                              # aadhaar photos saved here
│   │   │       │   └── upi_qr/                               # UPI QR images saved here
│   │   │       ├── wallet/
│   │   │       │   └── slips/                                # payment slips saved here
│   │   │       └── chat_attachments/                         # chat files/images saved here
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
├── cloudpanel-nginx/
│   └── wallet.bitxnow.com.conf                              # UPDATED
│       ├── root backend/public                              # FIXED from wrong public root
│       └── location ^~ /storage/                            # FINAL FIX
│           └── alias backend/storage/app/public/
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
    │   │   ├── auth_gate.dart                               # UPDATED removed duplicate initState
    │   │   ├── chat_screen.dart
    │   │   ├── chat_users_screen.dart
    │   │   ├── client_dashboard.dart
    │   │   ├── login_screen.dart                            # UPDATED UpdateService check + XynderLogo
    │   │   ├── merchant_dashboard.dart
    │   │   ├── merchant_requests_screen.dart
    │   │   ├── profile_screen.dart                          # USES ApiService.fixUrl()
    │   │   ├── register_screen.dart
    │   │   ├── request_screen.dart
    │   │   ├── transaction_detail_screen.dart
    │   │   └── wallet_transfer_screen.dart,history_screen.dart,settings_screen.dart
    │   │
    │   ├── services/
    │   │   ├── api_service.dart                             # UPDATED fixUrl /storage path
    │   │   └── update_service.dart                          # NEW auto update + force update + download + install
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
    │   │   ├── src/
    │   │   │   └── main/
    │   │   │       └── AndroidManifest.xml                  # UPDATED INTERNET + INSTALL_PACKAGES
    │   │   └── build.gradle                                 # UPDATED lint disabled, minify off
    │   │
    │   ├── gradle/
    │   │   └── wrapper/
    │   │       └── gradle-wrapper.properties                # UPDATED gradle-8.13-bin.zip
    │   │
    │   ├── build.gradle
    │   ├── gradle.properties                                # UPDATED JAVA_HOME JDK 17
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
    ├── pubspec.yaml                                         # UPDATED added packages
    └── README.md

========================================
CHANGED FILES SUMMARY
========================================

BACKEND (Laravel):
------------------
backend/bootstrap/app.php
  - Added: use Illuminate\Http\Middleware\HandleCors;
  - Added: $middleware->prepend(HandleCors::class);

backend/config/cors.php
  - Changed: allowed_origins => ['*']
  - Removed: allowed_origins_patterns
  - Changed: supports_credentials => false

backend/routes/api.php
  - Added: Route::options preflight handler
  - Added: Route::get('/version') endpoint
  - Added: force_update => true
  - Added: apk_url => wallet-mobile.apk

backend/public/apk/wallet-mobile.apk
  - UPLOADED: release APK hosted here for auto-update downloads


FLUTTER (wallet_mobile):
------------------------
wallet_mobile/pubspec.yaml
  - Added: package_info_plus: ^8.0.0
  - Added: dio: ^5.4.0
  - Added: open_file: ^3.3.2
  - Added: permission_handler: ^11.3.0
  - Added: path_provider: ^2.1.0

wallet_mobile/lib/services/update_service.dart  [NEW FILE]
  - CheckForUpdate() calls /api/version
  - Compares server version vs app version
  - Shows dialog if newer version found
  - force_update=true blocks back button
  - Downloads APK via Dio
  - Installs via OpenFile

wallet_mobile/lib/screens/auth_gate.dart
  - Removed: duplicate initState
  - Renamed: XynderLogo to _XynderSplashLogo
  - Renamed: _XynderLogoPainter to _XynderSplashLogoPainter
  - Removed: UpdateService import and call

wallet_mobile/lib/screens/login_screen.dart
  - Added: import update_service.dart
  - Added: UpdateService.checkForUpdate() in initState
  - Added: XynderLogo class at bottom (top level)
  - Added: _LoginXynderLogoPainter class at bottom (top level)
  - Removed: import auth_gate.dart

wallet_mobile/android/app/src/main/AndroidManifest.xml
  - Added: android.permission.INTERNET
  - Added: android.permission.ACCESS_NETWORK_STATE
  - Added: android.permission.REQUEST_INSTALL_PACKAGES
  - Added: android.permission.WRITE_EXTERNAL_STORAGE
  - Added: android.permission.READ_EXTERNAL_STORAGE
  - Added: android:usesCleartextTraffic="true"

wallet_mobile/android/app/build.gradle
  - Added: lint { checkReleaseBuilds = false; abortOnError = false }
  - Added: isMinifyEnabled = false
  - Added: isShrinkResources = false

wallet_mobile/android/gradle/wrapper/gradle-wrapper.properties
  - Changed: distributionUrl to gradle-8.13-bin.zip

wallet_mobile/android/gradle.properties
  - Added: org.gradle.java.home=C:\Program Files\Eclipse Adoptium\jdk-17.0.19.10-hotspot


========================================
VPS / DEPLOYMENT
========================================

Server path:
  /home/bitxnow-wallet/htdocs/wallet.bitxnow.com/backend/

Git branch in use:
  ui8

APK hosted at:
  https://wallet.bitxnow.com/apk/wallet-mobile.apk

Version API:
  https://wallet.bitxnow.com/api/version

Java installed:
  C:\Program Files\Eclipse Adoptium\jdk-17.0.19.10-hotspot  (Java 17)
  C:\Program Files\Eclipse Adoptium\jdk-25.0.3.9-hotspot    (Java 25 - not used)

Android SDK:
  C:\Users\User\AppData\Local\Android\Sdk

Flutter:
  C:\src\flutter


========================================
HOW TO DO NEXT UPDATE
========================================

Step 1 - VS Code:
  Edit code in wallet_mobile/ or backend/
  Change version in backend/routes/api.php: '1.0.2' -> '1.0.3'
  Change version in wallet_mobile/pubspec.yaml: 1.0.2+2 -> 1.0.3+3

Step 2 - VS Code Terminal:
  cd C:\Users\User\Desktop\Projects\X\wallet_mobile
  flutter build apk --release

Step 3 - CloudPanel File Manager:
  Upload: build\app\outputs\flutter-apk\app-release.apk
  Path:   htdocs/wallet.bitxnow.com/backend/public/apk/
  Rename: wallet-mobile.apk

Step 4 - VS Code Terminal:
  cd C:\Users\User\Desktop\Projects\X
  git add .
  git commit -m "v1.0.3 update"
  git push origin ui8

Step 5 - VPS SSH Terminal:
  cd /home/bitxnow-wallet/htdocs/wallet.bitxnow.com
  git fetch origin ui8
  git reset --hard origin/ui8
  cd backend
  php artisan optimize:clear

Step 6 - Verify:
  Open: https://wallet.bitxnow.com/api/version
  Check: "version" shows new number

Step 7 - Users:
  Open app -> update dialog appears -> tap Update Now -> done
flutter run -d chrome

