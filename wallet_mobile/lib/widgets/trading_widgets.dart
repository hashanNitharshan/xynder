import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

// Web-only imports
// ignore: avoid_web_libraries_in_flutter
import 'trading_widgets_web.dart' if (dart.library.io) 'trading_widgets_stub.dart';

Widget buildTickerWidget() {
  if (kIsWeb) return const WebTickerWidget();
  return const MobileTickerPlaceholder();
}

Widget buildNewsWidget() {
  if (kIsWeb) return const WebNewsWidget();
  return const MobileNewsPlaceholder();
}

class MobileTickerPlaceholder extends StatelessWidget {
  const MobileTickerPlaceholder({super.key});
  @override
  Widget build(BuildContext context) => const SizedBox.shrink();
}

class MobileNewsPlaceholder extends StatelessWidget {
  const MobileNewsPlaceholder({super.key});
  @override
  Widget build(BuildContext context) => const SizedBox.shrink();
}