import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import '../screens/update_screen.dart';

class UpdateService {
  // SharedPreferences key — stores the last version the user tapped "Later" on
  static const _skipKey = 'update_skipped_version';

  static Future<void> checkForUpdate(BuildContext context) async {
    try {
      // 1. Read installed version
      final info           = await PackageInfo.fromPlatform();
      final currentVersion = info.version;

      // 2. Ask server for latest version
      final res = await http
          .get(
            Uri.parse('${ApiService.baseUrl}/version'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 10));

      final data = ApiService.decode(res);
      if (data['success'] != true) return;

      final serverVersion = data['version']?.toString() ?? '0.0.0';
      final apkUrl        = data['apk_url']?.toString()  ?? '';
      final forceUpdate   = data['force_update'] == true;

      // 3. Nothing to do if already on latest
      if (!isNewer(serverVersion, currentVersion)) return;

      // 4. ✅ Skip check — only for optional updates.
      //    If the user already tapped "Later" for THIS exact version,
      //    don't show the screen again on the next launch.
      //    Force updates always show regardless.
      if (!forceUpdate) {
        final prefs         = await SharedPreferences.getInstance();
        final skippedVersion = prefs.getString(_skipKey) ?? '';
        if (skippedVersion == serverVersion) return; // already dismissed
      }

      if (!context.mounted) return;

      // 5. Navigate to full-page UpdateScreen.
      //    await blocks checkForUpdate() until user dismisses/installs.
      await Navigator.of(context).push(
        MaterialPageRoute(
          fullscreenDialog: true,
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

  /// Save the version the user chose to skip.
  /// Called by UpdateScreen when the user taps "Remind Me Later".
  static Future<void> markVersionSkipped(String version) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_skipKey, version);
    } catch (_) {}
  }

  /// Clear the skip record (useful after a successful install or for testing).
  static Future<void> clearSkip() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_skipKey);
    } catch (_) {}
  }

  /// Returns true when [server] is strictly greater than [current].
  /// e.g. isNewer("1.0.2", "1.0.1") → true
  static bool isNewer(String server, String current) {
    final s = server .split('.').map((e) => int.tryParse(e) ?? 0).toList();
    final c = current.split('.').map((e) => int.tryParse(e) ?? 0).toList();
    for (int i = 0; i < 3; i++) {
      final sv = s.length > i ? s[i] : 0;
      final cv = c.length > i ? c[i] : 0;
      if (sv > cv) return true;
      if (sv < cv) return false;
    }
    return false;
  }
}