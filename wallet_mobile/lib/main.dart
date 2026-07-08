import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';

import 'screens/auth_gate.dart';
import 'services/update_service.dart';

import 'utils/tv_iframe_registry.dart'
    if (dart.library.io) 'utils/tv_iframe_registry_stub.dart';

/// Shared across the whole app so the update screen can be pushed on top
/// of whatever the user is currently looking at, from anywhere — not just
/// from inside AuthGate.
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  if (kIsWeb) {
    registerTvIframes();
  }

  runApp(const WalletApp());
}

class WalletApp extends StatefulWidget {
  const WalletApp({super.key});

  @override
  State<WalletApp> createState() => _WalletAppState();
}

class _WalletAppState extends State<WalletApp> with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    // Initial check, once the first frame (and Navigator) exists.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      UpdateService.checkForUpdate(navigatorKey, force: true);
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // Re-check every time the app comes back to the foreground — e.g.
    // after the screen was turned off and back on — so a required
    // update can never be silently skipped just because AuthGate
    // already ran once at cold start.
    if (state == AppLifecycleState.resumed) {
      UpdateService.checkForUpdate(navigatorKey);
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'BitXnow',
      debugShowCheckedModeBanner: false,
      theme: ThemeData.dark().copyWith(
        scaffoldBackgroundColor: const Color(0xff0B0E11),
        primaryColor: const Color(0xffF0B90B),
        colorScheme: const ColorScheme.dark(
          primary: Color(0xffF0B90B),
          secondary: Color(0xffF0B90B),
          surface: Color(0xff1E2329),
        ),
      ),
      home: const AuthGate(),
    );
  }
}