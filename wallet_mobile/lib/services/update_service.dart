import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import '../screens/update_screen.dart';

class UpdateService {
  // ── SharedPreferences keys ──────────────────────────────────
  static const _skipKey       = 'update_skipped_version';
  static const _cacheVerKey   = 'update_cache_server_version';
  static const _cacheApkKey   = 'update_cache_apk_url';
  static const _cacheForceKey = 'update_cache_force_update';

  // Prevents a second update screen stacking on top if checkForUpdate()
  // fires twice close together (e.g. cold start + immediate resume).
  static bool _isShowing = false;

  // Avoid hammering /version on every screen-off/screen-on cycle.
  static DateTime? _lastCheckedAt;
  static const _cooldown = Duration(seconds: 20);

  /// Call this:
  ///   • once at app startup
  ///   • again every time the app resumes from background
  ///
  /// [navigatorKey] must be the SAME GlobalKey<NavigatorState> passed to
  /// MaterialApp, so the update screen can be pushed on top of whatever
  /// screen the user is currently on — not just the splash screen.
  static Future<void> checkForUpdate(
    GlobalKey<NavigatorState> navigatorKey, {
    bool force = false,
  }) async {
    if (_isShowing) return;

    if (!force &&
        _lastCheckedAt != null &&
        DateTime.now().difference(_lastCheckedAt!) < _cooldown) {
      return;
    }
    _lastCheckedAt = DateTime.now();

    try {
      final info = await PackageInfo.fromPlatform();
      final currentVersion = info.version;

      String? serverVersion;
      String apkUrl = '';
      bool forceUpdate = false;
      bool fromCache = false;

      try {
        final res = await http
            .get(
              Uri.parse('${ApiService.baseUrl}/version'),
              headers: {'Accept': 'application/json'},
            )
            .timeout(const Duration(seconds: 10));

        final data = ApiService.decode(res);
        if (data['success'] == true) {
          serverVersion = data['version']?.toString() ?? '0.0.0';
          apkUrl = data['apk_url']?.toString() ?? '';
          forceUpdate = data['force_update'] == true;

          // Remember this so a FUTURE failed check can still fail closed.
          await _cacheResult(serverVersion, apkUrl, forceUpdate);
        }
      } catch (_) {
        // Live check failed — fall back to the last known-good result
        // instead of silently letting the user through. This is what
        // makes "must update" survive a flaky/offline network.
        final cached = await _readCache();
        if (cached != null) {
          serverVersion = cached['version'] as String;
          apkUrl = cached['apk_url'] as String;
          forceUpdate = cached['force_update'] as bool;
          fromCache = true;
        }
      }

      // No live data and nothing cached — nothing we can safely enforce.
      if (serverVersion == null) return;
      if (!isNewer(serverVersion, currentVersion)) return;

      // Optional-update skip check — force updates always show regardless.
      if (!forceUpdate) {
        final prefs = await SharedPreferences.getInstance();
        final skipped = prefs.getString(_skipKey) ?? '';
        if (skipped == serverVersion) return;
      }

      if (navigatorKey.currentState == null) return;

      _isShowing = true;
      await navigatorKey.currentState!.push(
        MaterialPageRoute(
          fullscreenDialog: true,
          builder: (_) => UpdateScreen(
            currentVersion: currentVersion,
            serverVersion: serverVersion,
            apkUrl: apkUrl,
            forceUpdate: forceUpdate,
            isFromCache: fromCache,
          ),
        ),
      );
    } catch (_) {
      // Never crash the app because of a failed update check.
    } finally {
      _isShowing = false;
    }
  }

  static Future<void> _cacheResult(
      String version, String apkUrl, bool force) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_cacheVerKey, version);
      await prefs.setString(_cacheApkKey, apkUrl);
      await prefs.setBool(_cacheForceKey, force);
    } catch (_) {}
  }

  static Future<Map<String, Object>?> _readCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final version = prefs.getString(_cacheVerKey);
      if (version == null) return null;
      return {
        'version': version,
        'apk_url': prefs.getString(_cacheApkKey) ?? '',
        'force_update': prefs.getBool(_cacheForceKey) ?? false,
      };
    } catch (_) {
      return null;
    }
  }

  /// Called by UpdateScreen when the user taps "Later" (optional updates only).
  static Future<void> markVersionSkipped(String version) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_skipKey, version);
    } catch (_) {}
  }

  static Future<void> clearSkip() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_skipKey);
    } catch (_) {}
  }

  /// Returns true when [server] is strictly greater than [current].
  static bool isNewer(String server, String current) {
    final s = server.split('.').map((e) => int.tryParse(e) ?? 0).toList();
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