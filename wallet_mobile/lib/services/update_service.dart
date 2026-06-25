import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:open_file/open_file.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import 'api_service.dart';

class UpdateService {
  static Future<void> checkForUpdate(BuildContext context) async {
    try {
      final info = await PackageInfo.fromPlatform();
      final currentVersion = info.version;

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

      if (_isNewer(serverVersion, currentVersion)) {
        if (context.mounted) {
          // ✅ FIX: await the dialog so force_update blocks navigation
          await _showUpdateDialog(context, serverVersion, apkUrl, forceUpdate);
        }
      }
    } catch (_) {
      // Never crash the app because of a failed update check
    }
  }

  static bool _isNewer(String server, String current) {
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

  // ✅ FIX: returns Future so caller can await it
  static Future<void> _showUpdateDialog(
    BuildContext context,
    String version,
    String apkUrl,
    bool forceUpdate,
  ) {
    return showDialog(
      context: context,
      // ✅ FIX: respect forceUpdate flag — non-forced can be tapped away
      barrierDismissible: !forceUpdate,
      builder: (ctx) => _UpdateDialog(
        version: version,
        apkUrl: apkUrl,
        forceUpdate: forceUpdate,
      ),
    );
  }
}

// ─── Update Dialog ───────────────────────────────────────────────────────────

class _UpdateDialog extends StatefulWidget {
  final String version;
  final String apkUrl;
  final bool forceUpdate;

  const _UpdateDialog({
    required this.version,
    required this.apkUrl,
    required this.forceUpdate,
  });

  @override
  State<_UpdateDialog> createState() => _UpdateDialogState();
}

class _UpdateDialogState extends State<_UpdateDialog> {
  double _progress    = 0;
  bool   _downloading = false;
  bool   _hasError    = false; // ✅ ADDED: track error state separately
  String _status      = '';

  Future<void> _downloadAndInstall() async {
    setState(() {
      _downloading = true;
      _hasError    = false;
      _status      = 'Preparing...';
      _progress    = 0;
    });

    try {
      // ── 1. Permissions ────────────────────────────────────────────────────
      if (Platform.isAndroid) {
        await Permission.storage.request(); // needed on Android ≤ 9

        final installPerm = await Permission.requestInstallPackages.request();
        if (!installPerm.isGranted) {
          _setError('Enable "Install unknown apps" in Settings, then retry.');
          return;
        }
      }

      // ── 2. Download ───────────────────────────────────────────────────────
      // ✅ FIX: getApplicationDocumentsDirectory() is more reliable than
      //         getTemporaryDirectory() for APK install on Android 10+
      final dir      = await getApplicationDocumentsDirectory();
      final savePath = '${dir.path}/wallet_update.apk';

      // Remove any leftover file from a previous failed attempt
      final oldFile = File(savePath);
      if (await oldFile.exists()) await oldFile.delete();

      setState(() => _status = 'Downloading...');

      await Dio().download(
        widget.apkUrl,
        savePath,
        options: Options(receiveTimeout: const Duration(minutes: 10)),
        onReceiveProgress: (received, total) {
          if (total != -1) {
            setState(() {
              _progress = received / total;
              _status =
                  'Downloading ${(_progress * 100).toStringAsFixed(0)}%';
            });
          }
        },
      );

      // ── 3. Install ────────────────────────────────────────────────────────
      setState(() {
        _status   = 'Installing...';
        _progress = 1.0;
      });

      // ✅ FIX: check OpenResult so we can surface install errors
      final result = await OpenFile.open(
        savePath,
        type: 'application/vnd.android.package-archive',
      );

      if (result.type != ResultType.done) {
        _setError('Install failed: ${result.message}');
      }
      // If ResultType.done → Android system installer takes over
    } on DioException catch (e) {
      _setError('Download failed: ${e.message ?? "Network error"}');
    } catch (e) {
      _setError('Update failed. Please try again.');
    }
  }

  void _setError(String msg) => setState(() {
    _downloading = false;
    _hasError    = true;
    _status      = msg;
    _progress    = 0;
  });

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !widget.forceUpdate && !_downloading,
      onPopInvoked: (didPop) {
        if (!didPop && widget.forceUpdate) {
          // Force-update: pressing back exits the app entirely
          SystemNavigator.pop();
        }
      },
      child: AlertDialog(
        backgroundColor: const Color(0xff1a1a1a),
        title: const Row(
          children: [
            Icon(Icons.system_update_rounded,
                color: Color(0xffFFB800), size: 24),
            SizedBox(width: 8),
            Text(
              'Update Required',
              style: TextStyle(
                color: Color(0xffFFB800),
                fontSize: 16,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xff2a1500),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                    color: const Color(0xffFFB800).withOpacity(0.3)),
              ),
              child: Text(
                'Version ${widget.version} is available.\n\n'
                '${widget.forceUpdate ? "⚠️ This update is required to continue using the app." : "Update now for the latest features."}',
                style: const TextStyle(color: Colors.white70, height: 1.5),
              ),
            ),

            // ✅ FIX: unified status block — progress bar + status text in one place
            if (_downloading || _status.isNotEmpty) ...[
              const SizedBox(height: 16),
              if (_downloading)
                LinearProgressIndicator(
                  value: _progress > 0 ? _progress : null,
                  color: const Color(0xffFFB800),
                  backgroundColor: const Color(0xff2a2a2a),
                  minHeight: 6,
                ),
              if (_status.isNotEmpty) ...[
                const SizedBox(height: 8),
                Text(
                  _status,
                  style: TextStyle(
                    fontSize: 12,
                    // ✅ FIX: red for errors, subtle for progress
                    color: _hasError ? Colors.redAccent : Colors.white54,
                  ),
                ),
              ],
            ],
          ],
        ),
        actions: [
          if (!widget.forceUpdate && !_downloading)
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Later',
                  style: TextStyle(color: Colors.white38)),
            ),
          // ✅ Show button when not actively downloading,
          //    OR when there's an error (so user can retry)
          if (!_downloading || _hasError)
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xffFFB800),
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(
                    horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
              onPressed: _downloadAndInstall,
              icon: Icon(
                _hasError ? Icons.refresh_rounded : Icons.download_rounded,
                size: 18,
              ),
              label: Text(
                _hasError ? 'Retry' : 'Update Now',
                style: const TextStyle(fontWeight: FontWeight.w900),
              ),
            ),
        ],
      ),
    );
  }
}