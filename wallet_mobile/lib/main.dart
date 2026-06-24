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
      title: 'Xynder',
      debugShowCheckedModeBanner: false,
      theme: ThemeData.dark().copyWith(
        scaffoldBackgroundColor: const Color(0xff050805),
        primaryColor: const Color(0xff00ff5a),
        colorScheme: const ColorScheme.dark(
          primary: Color(0xff00ff5a),
          secondary: Color(0xff00ff5a),
          surface: Color(0xff101510),
        ),
      ),
      home: const AuthGate(),
    );
  }
}