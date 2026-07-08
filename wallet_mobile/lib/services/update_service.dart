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
  // Bypassed entirely when force: true (e.g. a manual "Check for Update" tap)
  // OR when a mandatory update is known to still be outstanding — see
  // _hasOutstandingMandatoryUpdate() below.
  static DateTime? _lastCheckedAt;
  static const _cooldown = Duration(seconds: 20);

  /// Call this:
  ///   • once at app startup
  ///   • again every time the app resumes from background
  ///   • again on the home/dashboard screen right after login — this is
  ///     what re-surfaces a mandatory update if it got cleared off the
  ///     nav stack by a pushAndRemoveUntil during the login transition
  ///   • with force: true for an explicit user-initiated check (e.g. a
  ///     "Check for Update" button in Settings) so it's never silently
  ///     skipped by the cooldown.
  ///
  /// [navigatorKey] must be the SAME GlobalKey<NavigatorState> passed to
  /// MaterialApp, so the update screen can be pushed on top of whatever
  /// screen the user is currently on — not just the splash screen.
  ///
  /// Returns true if an update was required (and the UpdateScreen was
  /// shown, or would have been had the navigator not been ready yet).
  /// Returns false if the app is already up to date, the update was
  /// already skipped, or nothing could be determined at all.
  static Future<bool> checkForUpdate(
    GlobalKey<NavigatorState> navigatorKey, {
    bool force = false,
  }) async {
    if (_isShowing) return false;

    // A mandatory (force_update) result that hasn't been resolved yet
    // must never be silently swallowed by the cooldown. Typical failure
    // case: the update screen is shown at startup, the login screen does
    // Navigator.pushAndRemoveUntil to reach home, which clears the update
    // screen off the stack, and home's own check — fired a couple of
    // seconds later — used to get blocked by the cooldown below. Now, if
    // the last known-good check says a mandatory update is still pending,
    // we treat this call as force regardless of the `force` argument.
    bool bypassCooldown = force;
    if (!bypassCooldown) {
      bypassCooldown = await _hasOutstandingMandatoryUpdate();
    }

    if (!bypassCooldown &&
        _lastCheckedAt != null &&
        DateTime.now().difference(_lastCheckedAt!) < _cooldown) {
      return false;
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
        // instead of silently letting the user through.
        final cached = await _readCache();
        if (cached != null) {
          serverVersion = cached['version'] as String;
          apkUrl = cached['apk_url'] as String;
          forceUpdate = cached['force_update'] as bool;
          fromCache = true;
        }
      }

      // No live data and nothing cached — nothing we can safely enforce.
      if (serverVersion == null) return false;
      if (!isNewer(serverVersion, currentVersion)) {
        // We're on the latest version — clear any stale mandatory-update
        // cache entry so it can't keep bypassing the cooldown forever.
        await _clearCacheIfResolved(currentVersion);
        return false;
      }

      // Optional-update skip check — force updates always show regardless.
      if (!forceUpdate) {
        final prefs = await SharedPreferences.getInstance();
        final skipped = prefs.getString(_skipKey) ?? '';
        if (skipped == serverVersion) return false;
      }

      if (navigatorKey.currentState == null) {
        // We know an update is needed, we just couldn't display it
        // right now (navigator not ready). Report it as needed anyway.
        return true;
      }

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
      return true;
    } catch (_) {
      // Never crash the app because of a failed update check.
      return false;
    } finally {
      _isShowing = false;
    }
  }

  /// True when the last known-good check says a mandatory update is
  /// still outstanding (i.e. the cached server version is force_update
  /// and is still newer than the currently-installed version). Used to
  /// force a fresh live check even inside the normal cooldown window.
  static Future<bool> _hasOutstandingMandatoryUpdate() async {
    try {
      final cached = await _readCache();
      if (cached == null) return false;

      final cachedForce = cached['force_update'] as bool;
      if (!cachedForce) return false;

      final cachedVersion = cached['version'] as String;
      final info = await PackageInfo.fromPlatform();
      return isNewer(cachedVersion, info.version);
    } catch (_) {
      return false;
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

  /// Drops the cached update record once the installed version has
  /// caught up to it, so a resolved mandatory update can't keep
  /// bypassing the cooldown indefinitely.
  static Future<void> _clearCacheIfResolved(String currentVersion) async {
    try {
      final cached = await _readCache();
      if (cached == null) return;
      final cachedVersion = cached['version'] as String;
      if (!isNewer(cachedVersion, currentVersion)) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove(_cacheVerKey);
        await prefs.remove(_cacheApkKey);
        await prefs.remove(_cacheForceKey);
      }
    } catch (_) {}
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