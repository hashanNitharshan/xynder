import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:open_file/open_file.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:path_provider/path_provider.dart';
import 'package:permission_handler/permission_handler.dart';
import '../services/api_service.dart';
import '../services/update_service.dart';
import 'login_screen.dart';

/// Works in two modes:
///   Mode 1 — Called by UpdateService: all params supplied, no extra fetch.
///   Mode 2 — Called from Settings:    no params, fetches /version itself.
class UpdateScreen extends StatefulWidget {
  final String? currentVersion;
  final String? serverVersion;
  final String? apkUrl;
  final bool    forceUpdate;

  const UpdateScreen({
    super.key,
    this.currentVersion,
    this.serverVersion,
    this.apkUrl,
    this.forceUpdate = false,
  });

  @override
  State<UpdateScreen> createState() => _UpdateScreenState();
}

class _UpdateScreenState extends State<UpdateScreen>
    with SingleTickerProviderStateMixin {

  // BitXnow blue -> purple palette
  static const _bg      = Color(0xff070A12);
  static const _surface = Color(0xff10131C);
  static const _border  = Color(0xff232A3A);
  static const _blue    = Color(0xff2E9BFF);
  static const _blueDeep= Color(0xff2563EB);
  static const _purple  = Color(0xff7C3AED);
  static const _purpleLt= Color(0xff9B6BFF);
  static const _muted   = Color(0xff8A93A6);

  static const _gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [_blue, _blueDeep, _purple],
  );
  static const _gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xff141826), Color(0xff0E1119), Color(0xff070A12)],
  );
  static const _gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [Color(0x552E9BFF), Color(0x337C3AED), Color(0x00000000)],
  );

  String _currentVer = '';
  String _serverVer  = '';
  String _apkUrl     = '';
  bool   _forceUpdt  = false;

  bool _fetching = false;
  bool _fetchErr = false;
  bool _upToDate = false;

  double _progress    = 0;
  bool   _downloading = false;
  bool   _hasError    = false;
  bool   _installed   = false;
  String _status      = '';

  late AnimationController _animCtrl;
  late Animation<double>   _fade;
  late Animation<Offset>   _slide;

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    );
    _fade  = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _slide = Tween<Offset>(
      begin: const Offset(0, 0.07),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut));

    if (widget.serverVersion != null) {
      // Mode 1: pre-filled by UpdateService
      _currentVer = widget.currentVersion ?? '';
      _serverVer  = widget.serverVersion!;
      _apkUrl     = widget.apkUrl ?? '';
      _forceUpdt  = widget.forceUpdate;
      _animCtrl.forward();
    } else {
      // Mode 2: Settings — fetch ourselves
      _fetchVersionInfo();
    }
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchVersionInfo() async {
    setState(() { _fetching = true; _fetchErr = false; _upToDate = false; });

    try {
      final info = await PackageInfo.fromPlatform();
      _currentVer = info.version;

      final res = await http
          .get(
            Uri.parse('${ApiService.baseUrl}/version'),
            headers: {'Accept': 'application/json'},
          )
          .timeout(const Duration(seconds: 10));

      final data = ApiService.decode(res);

      if (data['success'] == true) {
        final sv = data['version']?.toString() ?? '0.0.0';
        final isNewer = UpdateService.isNewer(sv, _currentVer);

        setState(() {
          _serverVer = sv;
          _apkUrl    = data['apk_url']?.toString() ?? '';
          _forceUpdt = data['force_update'] == true;
          _fetching  = false;
          // Treat as "up to date" if server isn't newer than installed.
          // This naturally covers the post-install case: once the user
          // actually opens the newly installed APK, PackageInfo reports
          // the new version, so server is no longer "newer" → up to date.
          _upToDate  = !isNewer;
        });
      } else {
        setState(() { _fetching = false; _fetchErr = true; });
      }
    } catch (_) {
      setState(() { _fetching = false; _fetchErr = true; });
    }

    _animCtrl..reset()..forward();
  }

  Future<void> _dismissUpdate() async {
    await UpdateService.markVersionSkipped(_serverVer);
    if (mounted) Navigator.pop(context);
  }

  Future<void> _downloadAndInstall() async {
    setState(() {
      _downloading = true;
      _hasError    = false;
      _installed   = false;
      _status      = 'Preparing...';
      _progress    = 0;
    });

    try {
      if (Platform.isAndroid) {
        await Permission.storage.request();
        final perm = await Permission.requestInstallPackages.request();
        if (!perm.isGranted) {
          _setError('Enable "Install unknown apps" in Settings, then retry.');
          return;
        }
      }

      final dir      = await getApplicationDocumentsDirectory();
      final savePath = '${dir.path}/wallet_update.apk';
      final old      = File(savePath);
      if (await old.exists()) await old.delete();

      setState(() => _status = 'Downloading...');

      await Dio().download(
        _apkUrl,
        savePath,
        options: Options(receiveTimeout: const Duration(minutes: 10)),
        onReceiveProgress: (received, total) {
          if (total != -1) {
            setState(() {
              _progress = received / total;
              _status   = 'Downloading ${(_progress * 100).toStringAsFixed(0)}%';
            });
          }
        },
      );

      setState(() { _status = 'Installing...'; _progress = 1.0; });

      final result = await OpenFile.open(
        savePath,
        type: 'application/vnd.android.package-archive',
      );

      if (result.type != ResultType.done) {
        _setError('Install failed: ${result.message}');
      } else {
        // Mark this server version as handled so neither the auto-check
        // nor the Settings button nags about it again before the user
        // actually opens the newly installed APK.
        await UpdateService.markVersionSkipped(_serverVer);
        setState(() {
          _installed   = true;
          _downloading = false;
          _status      = 'Follow the on-screen prompts to complete installation.';
        });
      }
    } on DioException catch (e) {
      _setError('Download failed: ${e.message ?? "Network error"}');
    } catch (_) {
      _setError('Update failed. Please try again.');
    }
  }

  void _setError(String msg) => setState(() {
        _downloading = false;
        _hasError    = true;
        _status      = msg;
        _progress    = 0;
      });

  Widget _glowBall(double size, double opacity, Color color) => Container(
        width: size, height: size,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: color.withOpacity(opacity),
        ),
      );

  Widget _divider() => Container(
        height: 1,
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: [
            Colors.transparent,
            _blue.withOpacity(0.3),
            Colors.transparent,
          ]),
        ),
      );

  Widget _bullet(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Row(
          children: [
            Container(width: 5, height: 5,
                decoration: const BoxDecoration(
                    shape: BoxShape.circle, color: _blue)),
            const SizedBox(width: 10),
            Expanded(
              child: Text(text,
                  style: const TextStyle(
                      color: Colors.white60, fontSize: 12, height: 1.4)),
            ),
          ],
        ),
      );

  Widget _versionChip({
    required String label,
    required String version,
    required Color  color,
    bool isNew = false,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
        decoration: BoxDecoration(
          color: color.withOpacity(0.07),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: color.withOpacity(0.22)),
        ),
        child: Column(
          children: [
            Text(label,
                style: TextStyle(
                    color: color.withOpacity(0.65),
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.2)),
            const SizedBox(height: 8),
            Text('v$version',
                style: TextStyle(
                    color: color, fontSize: 22, fontWeight: FontWeight.w900)),
            if (isNew) ...[
              const SizedBox(height: 6),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _blue.withOpacity(0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text('LATEST',
                    style: TextStyle(
                        color: _blue,
                        fontSize: 9,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 1.0)),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _gradientBtn(
      {required String label,
      required IconData icon,
      required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity, height: 56,
        decoration: BoxDecoration(
          gradient: _gradientAccent,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
                color: _purple.withOpacity(0.35),
                blurRadius: 18,
                offset: const Offset(0, 7))
          ],
        ),
        child: Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: Colors.white, size: 20),
              const SizedBox(width: 9),
              Text(label,
                  style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 16)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _ghostBtn(
      {required String label, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity, height: 50,
        decoration: BoxDecoration(
          color: _surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: _border),
        ),
        child: Center(
          child: Text(label,
              style: const TextStyle(
                  color: _muted,
                  fontWeight: FontWeight.w700,
                  fontSize: 14)),
        ),
      ),
    );
  }

  Widget _statusBox() {
    final iconData  = _hasError
        ? Icons.error_outline_rounded
        : _installed
            ? Icons.check_circle_outline_rounded
            : Icons.download_rounded;
    final iconColor = _hasError
        ? Colors.redAccent
        : _installed ? Colors.greenAccent : _blue;

    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: _surface.withOpacity(0.6),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: _hasError
                  ? Colors.red.withOpacity(0.25)
                  : _installed
                      ? Colors.green.withOpacity(0.25)
                      : _blue.withOpacity(0.15),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(iconData, color: iconColor, size: 16),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(_status,
                        style: TextStyle(
                            color: _hasError
                                ? Colors.redAccent
                                : _installed
                                    ? Colors.greenAccent
                                    : Colors.white70,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            height: 1.4)),
                  ),
                  if (_downloading && _progress > 0)
                    Text('${(_progress * 100).toStringAsFixed(0)}%',
                        style: const TextStyle(
                            color: _blue,
                            fontSize: 13,
                            fontWeight: FontWeight.w900)),
                ],
              ),
              if (_downloading) ...[
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: LinearProgressIndicator(
                    value: _progress > 0 ? _progress : null,
                    backgroundColor: _border,
                    valueColor: const AlwaysStoppedAnimation(_blue),
                    minHeight: 8,
                  ),
                ),
              ],
            ],
          ),
        ),
        if (_downloading) ...[
          const SizedBox(height: 10),
          const Text('Do not close the app during download',
              style: TextStyle(color: Colors.white24, fontSize: 11)),
        ],
      ],
    );
  }

  Widget _loadingBody() => const SizedBox(
        height: 260,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(color: _blue, strokeWidth: 2.5),
              SizedBox(height: 20),
              Text('Checking for updates...',
                  style: TextStyle(color: _muted, fontSize: 14)),
            ],
          ),
        ),
      );

  Widget _errorBody() => Column(
        children: [
          const Icon(Icons.wifi_off_rounded, color: _muted, size: 52),
          const SizedBox(height: 16),
          const Text('Unable to Check',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w900)),
          const SizedBox(height: 8),
          const Text(
            'Could not reach the update server.\nCheck your connection and try again.',
            textAlign: TextAlign.center,
            style: TextStyle(color: _muted, fontSize: 13, height: 1.6),
          ),
          const SizedBox(height: 28),
          _gradientBtn(
              label: 'Try Again',
              icon: Icons.refresh_rounded,
              onTap: _fetchVersionInfo),
        ],
      );

  Widget _upToDateBody() => Column(
        children: [
          Container(
            width: 84, height: 84,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.green.withOpacity(0.08),
              border:
                  Border.all(color: Colors.green.withOpacity(0.4), width: 2),
              boxShadow: [
                BoxShadow(
                    color: Colors.green.withOpacity(0.2), blurRadius: 28)
              ],
            ),
            child: const Icon(Icons.check_rounded,
                color: Colors.greenAccent, size: 44),
          ),
          const SizedBox(height: 20),
          const Text("You're Up to Date!",
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.w900)),
          const SizedBox(height: 8),
          const Text('BitXnow Wallet is running the\nlatest version.',
              textAlign: TextAlign.center,
              style: TextStyle(color: _muted, fontSize: 13, height: 1.6)),
          const SizedBox(height: 28),
          Row(
            children: [
              _versionChip(
                  label: 'INSTALLED',
                  version: _currentVer,
                  color: Colors.greenAccent),
              const SizedBox(width: 12),
              _versionChip(
                  label: 'LATEST',
                  version: _serverVer,
                  color: _blue,
                  isNew: true),
            ],
          ),
          const SizedBox(height: 20),
          _ghostBtn(label: 'Check Again', onTap: _fetchVersionInfo),
        ],
      );

  Widget _updateBody() => Column(
        children: [
          Container(
            padding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
            decoration: BoxDecoration(
              color: _forceUpdt
                  ? Colors.red.withOpacity(0.12)
                  : _blue.withOpacity(0.10),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: _forceUpdt
                    ? Colors.red.withOpacity(0.35)
                    : _blue.withOpacity(0.30),
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  _forceUpdt
                      ? Icons.warning_amber_rounded
                      : Icons.system_update_rounded,
                  color: _forceUpdt ? Colors.redAccent : _blue,
                  size: 14,
                ),
                const SizedBox(width: 6),
                Text(
                  _forceUpdt ? 'UPDATE REQUIRED' : 'UPDATE AVAILABLE',
                  style: TextStyle(
                    color: _forceUpdt ? Colors.redAccent : _blue,
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          ShaderMask(
            shaderCallback: (b) => _gradientAccent.createShader(b),
            child: const Text('New Version Ready',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 27,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.5)),
          ),
          const SizedBox(height: 8),
          Text(
            _forceUpdt
                ? 'This update is required to continue\nusing the BitXnow Wallet app.'
                : 'A newer version of BitXnow Wallet\nis available for download.',
            textAlign: TextAlign.center,
            style: const TextStyle(color: _muted, fontSize: 13, height: 1.6),
          ),
          const SizedBox(height: 28),
          Row(
            children: [
              _versionChip(
                  label: 'CURRENT', version: _currentVer, color: _muted),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12),
                child: Icon(Icons.arrow_forward_rounded,
                    color: _blue.withOpacity(0.6), size: 22),
              ),
              _versionChip(
                  label: 'NEW',
                  version: _serverVer,
                  color: _blue,
                  isNew: true),
            ],
          ),
          const SizedBox(height: 24),
          _divider(),
          const SizedBox(height: 24),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xff0E1626).withOpacity(0.6),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: _blue.withOpacity(0.15)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.auto_awesome_rounded, color: _blue, size: 14),
                    SizedBox(width: 6),
                    Text("WHAT'S NEW",
                        style: TextStyle(
                            color: _blue,
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.1)),
                  ],
                ),
                const SizedBox(height: 10),
                _bullet('Performance improvements'),
                _bullet('Improved security & stability'),
                _bullet('Latest feature updates'),
              ],
            ),
          ),
          const SizedBox(height: 20),
          if (_downloading || _hasError || _installed) ...[
            _statusBox(),
            const SizedBox(height: 20),
          ],
          if (!_downloading || _hasError) ...[
            _gradientBtn(
              label: _hasError ? 'Retry Download' : 'Update Now',
              icon: _hasError
                  ? Icons.refresh_rounded
                  : Icons.download_rounded,
              onTap: _downloadAndInstall,
            ),
            if (!_forceUpdt) ...[
              const SizedBox(height: 12),
              _ghostBtn(
                label: 'Remind Me Later',
                onTap: _dismissUpdate,
              ),
            ],
          ],
          if (_forceUpdt && !_downloading && !_installed) ...[
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.info_outline_rounded,
                    color: Colors.white24, size: 13),
                const SizedBox(width: 6),
                Text('Back button will exit the app',
                    style: TextStyle(
                        color: Colors.white.withOpacity(0.2),
                        fontSize: 11)),
              ],
            ),
          ],
        ],
      );

  Widget _card(Widget child) => Container(
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          gradient: _gradientCard,
          borderRadius: BorderRadius.circular(32),
          border: Border.all(color: const Color(0xff232A3A)),
          boxShadow: [
            BoxShadow(
                color: _purple.withOpacity(0.25),
                blurRadius: 55,
                offset: const Offset(0, 24))
          ],
        ),
        child: Stack(
          children: [
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(32),
                  gradient: _gradientGlow,
                ),
              ),
            ),
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 100, height: 100,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withOpacity(0.05),
                    border: Border.all(color: _blue, width: 2),
                    boxShadow: [
                      BoxShadow(
                          color: _purple.withOpacity(0.30), blurRadius: 32)
                    ],
                  ),
                  child: Image.asset(
                    "assets/images/bitxnow_logo.jpeg",
                    fit: BoxFit.contain,
                  ),
                ),
                const SizedBox(height: 20),
                child,
              ],
            ),
          ],
        ),
      );

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_forceUpdt && !_downloading,
      onPopInvoked: (didPop) {
        if (!didPop && _forceUpdt) SystemNavigator.pop();
      },
      child: Scaffold(
        backgroundColor: _bg,
        body: Stack(
          children: [
            Positioned(top: -110, left: -80,
                child: _glowBall(270, 0.10, _blue)),
            Positioned(bottom: -130, right: -80,
                child: _glowBall(310, 0.10, _purple)),
            Positioned(top: 140, right: 60,
                child: _glowBall(80, 0.06, _blue)),

            SafeArea(
              child: Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(22),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 460),
                    child: _fetching
                        ? _card(_loadingBody())
                        : FadeTransition(
                            opacity: _fade,
                            child: SlideTransition(
                              position: _slide,
                              child: _card(
                                _fetchErr
                                    ? _errorBody()
                                    : _upToDate
                                        ? _upToDateBody()
                                        : _updateBody(),
                              ),
                            ),
                          ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}