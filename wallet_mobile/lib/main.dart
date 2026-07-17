import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import 'screens/auth_gate.dart';
import 'services/update_service.dart';
import 'utils/tv_iframe_registry.dart'
    if (dart.library.io) 'utils/tv_iframe_registry_stub.dart';

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
