import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'api_service.dart';
import '../screens/update_screen.dart'; // ← new full-page screen

class UpdateService {
  static Future<void> checkForUpdate(BuildContext context) async {
    try {
      // 1. Read the app's installed version
      final info           = await PackageInfo.fromPlatform();
      final currentVersion = info.version;

      // 2. Ask the server for the latest version
      final res = await http
          .get(
            Uri.parse('${ApiService.baseUrl}/version'),
            headers: {"Accept": "application/json"},
          )
          .timeout(const Duration(seconds: 10));

      final data = ApiService.decode(res);
      if (data['success'] != true) return;

      final serverVersion = data['version']?.toString() ?? '0.0.0';
      final apkUrl        = data['apk_url']?.toString() ?? '';
      final forceUpdate   = data['force_update'] == true;

      // 3. Nothing to do if the app is already up to date
      if (!_isNewer(serverVersion, currentVersion)) return;
      if (!context.mounted) return;

      // 4. Navigate to the full-page UpdateScreen.
      //    Using `await` here blocks checkForUpdate() until the user
      //    dismisses the screen (same blocking behaviour as the old dialog).
      await Navigator.of(context).push(
        MaterialPageRoute(
          fullscreenDialog: true, // slides up from bottom on iOS/Android
          builder: (_) => UpdateScreen(
            currentVersion: currentVersion,
            serverVersion: serverVersion,
            apkUrl: apkUrl,
            forceUpdate: forceUpdate,
          ),
        ),
      );
    } catch (_) {
      // Never crash the app because of a failed update check
    }
  }

  // Returns true when serverVersion is strictly greater than currentVersion.
  // e.g. _isNewer("1.0.2", "1.0.1") → true
  static bool _isNewer(String server, String current) {
    final s = server .split('.').map((e) => int.tryParse(e) ?? 0).toList();
    final c = current.split('.').map((e) => int.tryParse(e) ?? 0).toList();
    for (int i = 0; i < 3; i++) {
      final sv = s.length > i ? s[i] : 0;
      final cv = c.length > i ? c[i] : 0;
      if (sv > cv) return true;
      if (sv < cv) return false;
    }
    return false; // equal versions → no update needed
  }
}