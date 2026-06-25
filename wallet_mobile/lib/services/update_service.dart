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

      if (!_isNewer(serverVersion, currentVersion)) return;
      if (!context.mounted) return;

      // ✅ THE FIX: await the dialog so checkLogin() is BLOCKED until
      //    the user dismisses it (or installs the update and app restarts).
      //    Without this await, login page loaded immediately on top of dialog.
      await _showUpdateDialog(context, serverVersion, apkUrl, forceUpdate);
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

  // ✅ THE FIX: was void — now Future<void> so it can be awaited
  static Future<void> _showUpdateDialog(
    BuildContext context,
    String version,
    String apkUrl,
    bool forceUpdate,
  ) async {
    // ✅ THE FIX: was missing await — now awaits until dialog is dismissed
    await showDialog(
      context: context,
      barrierDismissible: !forceUpdate, // force=true → cannot tap outside
      builder: (ctx) => _UpdateDialog(
        version: version,
        apkUrl: apkUrl,
        forceUpdate: forceUpdate,
      ),
    );
  }
}

// ─── Update Dialog ────────────────────────────────────────────────────────────

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
  bool   _hasError    = false;
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
        await Permission.storage.request();

        final installPerm = await Permission.requestInstallPackages.request();
        if (!installPerm.isGranted) {
          _setError('Enable "Install unknown apps" in Settings, then retry.');
          return;
        }
      }

      // ── 2. Download ───────────────────────────────────────────────────────
      final dir      = await getApplicationDocumentsDirectory();
      final savePath = '${dir.path}/wallet_update.apk';

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

      final result = await OpenFile.open(
        savePath,
        type: 'application/vnd.android.package-archive',
      );

      if (result.type != ResultType.done) {
        _setError('Install failed: ${result.message}');
      }
      // If done → Android system installer takes over
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
          SystemNavigator.pop(); // back button exits app on force update
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