import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';

import 'screens/auth_gate.dart';

import 'utils/tv_iframe_registry.dart'
    if (dart.library.io) 'utils/tv_iframe_registry_stub.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  if (kIsWeb) {
    registerTvIframes();
  }

  runApp(const WalletApp());
}

class WalletApp extends StatelessWidget {
  const WalletApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
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