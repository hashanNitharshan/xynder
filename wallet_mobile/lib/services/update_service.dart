import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
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
      barrierDismissible: !forceUpdate,
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
        _status = 'Failed. Try again.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: const Color(0xff1a1a1a),
      title: const Text(
        'Update Available',
        style: TextStyle(color: Color(0xff00ff5a)),
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            'Version ${widget.version} is available.\nUpdate now for latest features.',
            style: const TextStyle(color: Colors.white70),
          ),
          if (_downloading) ...[
            const SizedBox(height: 16),
            LinearProgressIndicator(
              value: _progress > 0 ? _progress : null,
              color: const Color(0xff00ff5a),
            ),
            const SizedBox(height: 8),
            Text(
              _status,
              style: const TextStyle(fontSize: 12, color: Colors.white54),
            ),
          ],
        ],
      ),
      actions: [
        if (!widget.forceUpdate && !_downloading)
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text(
              'Later',
              style: TextStyle(color: Colors.white54),
            ),
          ),
        if (!_downloading)
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xff00ff5a),
              foregroundColor: Colors.black,
            ),
            onPressed: _downloadAndInstall,
            child: const Text('Update Now'),
          ),
      ],
    );
  }
}