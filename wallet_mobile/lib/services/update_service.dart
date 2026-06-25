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

      final res = await http.get(
        Uri.parse('${ApiService.baseUrl}/version'),
        headers: {"Accept": "application/json"},
      ).timeout(const Duration(seconds: 10));

      final data = ApiService.decode(res);
      if (data['success'] != true) return;

      final serverVersion = data['version']?.toString() ?? '0.0.0';
      final apkUrl = data['apk_url']?.toString() ?? '';
      final forceUpdate = data['force_update'] == true;

      if (_isNewer(serverVersion, currentVersion)) {
        if (context.mounted) {
          _showUpdateDialog(context, serverVersion, apkUrl, forceUpdate);
        }
      }
    } catch (_) {}
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

  static void _showUpdateDialog(
    BuildContext context,
    String version,
    String apkUrl,
    bool forceUpdate,
  ) {
    showDialog(
      context: context,
      barrierDismissible: false, // ← always false — can't tap outside
      builder: (ctx) => _UpdateDialog(
        version: version,
        apkUrl: apkUrl,
        forceUpdate: forceUpdate,
      ),
    );
  }
}

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
  double _progress = 0;
  bool _downloading = false;
  String _status = '';

  Future<void> _downloadAndInstall() async {
    setState(() {
      _downloading = true;
      _status = 'Preparing...';
    });

    try {
      await Permission.requestInstallPackages.request();

      final dir = await getTemporaryDirectory();
      final savePath = '${dir.path}/update.apk';

      setState(() => _status = 'Downloading...');

      final dio = Dio();
      await dio.download(
        widget.apkUrl,
        savePath,
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

      setState(() => _status = 'Installing...');
      await OpenFile.open(savePath);
    } catch (e) {
      setState(() {
        _downloading = false;
        _status = 'Download failed. Try again.';
      });
    }
  }

  // ✅ Block Android back button on force update
  Future<bool> _onWillPop() async {
    if (widget.forceUpdate) {
      // Close app instead of dismissing dialog
      SystemNavigator.pop();
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !widget.forceUpdate,
      onPopInvoked: (didPop) {
        if (!didPop && widget.forceUpdate) {
          SystemNavigator.pop();
        }
      },
      child: AlertDialog(
        backgroundColor: const Color(0xff1a1a1a),
        title: Row(
          children: [
            const Icon(Icons.system_update_rounded,
                color: Color(0xffFFB800), size: 24),
            const SizedBox(width: 8),
            const Text(
              'Update Required',
              style: TextStyle(
                  color: Color(0xffFFB800),
                  fontSize: 16,
                  fontWeight: FontWeight.w900),
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
                border: Border.all(color: const Color(0xffFFB800).withOpacity(0.3)),
              ),
              child: Text(
                'Version ${widget.version} is available.\n\n'
                '${widget.forceUpdate ? "⚠️ This update is required to continue using the app." : "Update now for latest features."}',
                style: const TextStyle(color: Colors.white70, height: 1.5),
              ),
            ),
            if (_downloading) ...[
              const SizedBox(height: 16),
              LinearProgressIndicator(
                value: _progress > 0 ? _progress : null,
                color: const Color(0xffFFB800),
                backgroundColor: const Color(0xff2a2a2a),
              ),
              const SizedBox(height: 8),
              Text(
                _status,
                style:
                    const TextStyle(fontSize: 12, color: Colors.white54),
              ),
            ],
            if (_status == 'Download failed. Try again.') ...[
              const SizedBox(height: 8),
              Text(
                _status,
                style: const TextStyle(
                    fontSize: 12, color: Colors.redAccent),
              ),
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
          if (!_downloading)
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
              icon: const Icon(Icons.download_rounded, size: 18),
              label: const Text('Update Now',
                  style: TextStyle(fontWeight: FontWeight.w900)),
            ),
        ],
      ),
    );
  }
}