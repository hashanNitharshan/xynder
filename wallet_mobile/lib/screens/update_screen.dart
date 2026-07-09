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

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - DARK YELLOW / BLACK ONLY (no light yellow)
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff0D0D0D);
  static const surfaceAlt = Color(0xff171717);

  static const border = Color(0xff2E2E2E);
  static const borderFaint = Color(0xff202020);

  // Only dark/deep yellow tones — no bright/light yellow anywhere.
  static const orange = Color(0xffB8860B); // dark goldenrod
  static const amber = Color(0xff7A5300); // deep amber (darker than before)
  static const gold = Color(0xffA67C00); // muted dark gold highlight

  static const red = Color(0xffEF4444);
  static const green = Color(0xff22C55E);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xffA3A3A3);

  static const gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [
      Color(0xff5C4200),
      Color(0xff8A6300),
      Color(0xffA67C00),
    ],
  );

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff000000),
      Color(0xff0D0D0D),
      Color(0xff141000),
    ],
  );

  static const gradientGlow = RadialGradient(
    center: Alignment(-0.2, -0.6),
    radius: 1.2,
    colors: [
      Color(0x44B8860B),
      Color(0x22A67C00),
      Color(0x00000000),
    ],
  );
}

/// ─────────────────────────────────────────────────────────────
/// IMPORTANT — set this to your real post-login/home route name.
/// After a forced update finishes installing, the app sends the
/// user here instead of just closing the screen.
/// ─────────────────────────────────────────────────────────────
const String kHomeOrLoginRoute = '/login';

/// Works in two modes:
///   Mode 1 — Called by UpdateService: all params supplied, no extra fetch.
///   Mode 2 — Called from Settings:    no params, fetches /version itself.
class UpdateScreen extends StatefulWidget {
  final String? currentVersion;
  final String? serverVersion;
  final String? apkUrl;
  final bool forceUpdate;
  final bool isFromCache;

  const UpdateScreen({
    super.key,
    this.currentVersion,
    this.serverVersion,
    this.apkUrl,
    this.forceUpdate = false,
    this.isFromCache = false,
  });

  @override
  State<UpdateScreen> createState() => _UpdateScreenState();
}

class _UpdateScreenState extends State<UpdateScreen>
    with SingleTickerProviderStateMixin {
  String _currentVer = '';
  String _serverVer = '';
  String _apkUrl = '';
  bool _forceUpdt = false;
  bool _fromCache = false;

  bool _fetching = false;
  bool _fetchErr = false;
  bool _upToDate = false;

  double _progress = 0;
  bool _downloading = false;
  bool _hasError = false;
  bool _installed = false;
  String _status = '';

  late AnimationController _anim;
  late Animation<double> _fade;
  late Animation<Offset> _slide;

  // Spinner ring shown around the logo while downloading/installing,
  // mimicking the native Android system-update look.
  late AnimationController _spin;

  @override
  void initState() {
    super.initState();
    _anim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    );
    _fade = CurvedAnimation(parent: _anim, curve: Curves.easeOut);
    _slide = Tween<Offset>(
      begin: const Offset(0, 0.05),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _anim, curve: Curves.easeOut));

    _spin = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat();

    if (widget.serverVersion != null) {
      // Mode 1: pre-filled by UpdateService
      _currentVer = widget.currentVersion ?? '';
      _serverVer = widget.serverVersion!;
      _apkUrl = widget.apkUrl ?? '';
      _forceUpdt = widget.forceUpdate;
      _fromCache = widget.isFromCache;
      _anim.forward();
    } else {
      // Mode 2: Settings — fetch ourselves
      _fetchVersionInfo();
    }
  }

  @override
  void dispose() {
    _anim.dispose();
    _spin.dispose();
    super.dispose();
  }

  Future<void> _fetchVersionInfo() async {
    setState(() {
      _fetching = true;
      _fetchErr = false;
      _upToDate = false;
    });

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
          _apkUrl = data['apk_url']?.toString() ?? '';
          _forceUpdt = data['force_update'] == true;
          _fromCache = false;
          _fetching = false;
          _upToDate = !isNewer;
        });
      } else {
        setState(() {
          _fetching = false;
          _fetchErr = true;
        });
      }
    } catch (_) {
      setState(() {
        _fetching = false;
        _fetchErr = true;
      });
    }

    _anim
      ..reset()
      ..forward();
  }

  Future<void> _dismissUpdate() async {
    await UpdateService.markVersionSkipped(_serverVer);
    if (mounted) Navigator.pop(context);
  }

  Future<void> _downloadAndInstall() async {
    setState(() {
      _downloading = true;
      _hasError = false;
      _installed = false;
      _status = '';
      _progress = 0;
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

      final dir = await getApplicationDocumentsDirectory();
      final savePath = '${dir.path}/wallet_update.apk';
      final old = File(savePath);
      if (await old.exists()) await old.delete();

      await Dio().download(
        _apkUrl,
        savePath,
        options: Options(receiveTimeout: const Duration(minutes: 10)),
        onReceiveProgress: (received, total) {
          if (total != -1) {
            setState(() {
              _progress = received / total;
            });
          }
        },
      );

      setState(() => _progress = 1.0);

      final result = await OpenFile.open(
        savePath,
        type: 'application/vnd.android.package-archive',
      );

      if (result.type != ResultType.done) {
        _setError('Install failed. Please try again.');
      } else {
        await UpdateService.markVersionSkipped(_serverVer);
        setState(() {
          _installed = true;
          _downloading = false;
        });

        // If this was a mandatory update, move the user forward to the
        // home/login flow instead of leaving them stuck on this screen.
        if (_forceUpdt) {
          _goToHomeOrLogin();
        }
      }
    } on DioException catch (_) {
      _setError('Network error. Please try again.');
    } catch (_) {
      _setError('Something went wrong. Please try again.');
    }
  }

  void _goToHomeOrLogin() {
    if (!mounted) return;
    Navigator.of(context).pushNamedAndRemoveUntil(
      kHomeOrLoginRoute,
      (route) => false,
    );
  }

  void _setError(String msg) => setState(() {
        _downloading = false;
        _hasError = true;
        _status = msg;
        _progress = 0;
      });

  Widget _glowBall(double size, double opacity, Color color) => Container(
        width: size,
        height: size,
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
            _C.gold.withOpacity(0.3),
            Colors.transparent,
          ]),
        ),
      );

  Widget _versionChip({
    required String label,
    required String version,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: color.withOpacity(0.25)),
        ),
        child: Column(
          children: [
            Text(
              label,
              style: TextStyle(
                color: color.withOpacity(0.7),
                fontSize: 10,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'v$version',
              style: TextStyle(
                color: color,
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _gradientBtn({
    required String label,
    required VoidCallback onTap,
    IconData? icon,
    bool loading = false,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        width: double.infinity,
        height: 54,
        decoration: BoxDecoration(
          gradient: _C.gradientAccent,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: _C.orange.withOpacity(0.3),
              blurRadius: 16,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Center(
          child: loading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.3,
                    color: Colors.white,
                  ),
                )
              : Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (icon != null) ...[
                      Icon(icon, color: Colors.white, size: 18),
                      const SizedBox(width: 8),
                    ],
                    Text(
                      label,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 15,
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _ghostBtn({required String label, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        height: 48,
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: _C.border),
        ),
        child: Center(
          child: Text(
            label,
            style: const TextStyle(
              color: _C.textSecondary,
              fontWeight: FontWeight.w700,
              fontSize: 14,
            ),
          ),
        ),
      ),
    );
  }

  /// Logo with a spinning dark-gold ring around it — the Android
  /// system-update look, recolored to dark yellow / black only.
  Widget _logoWithSpinner({required bool spinning}) {
    return SizedBox(
      width: 120,
      height: 120,
      child: Stack(
        alignment: Alignment.center,
        children: [
          if (spinning)
            AnimatedBuilder(
              animation: _spin,
              builder: (context, child) => Transform.rotate(
                angle: _spin.value * 6.28318,
                child: child,
              ),
              child: SizedBox(
                width: 120,
                height: 120,
                child: CircularProgressIndicator(
                  strokeWidth: 3,
                  valueColor: const AlwaysStoppedAnimation(_C.gold),
                  backgroundColor: _C.border,
                ),
              ),
            )
          else
            Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: _C.gold, width: 3),
              ),
            ),
          Container(
            width: 88,
            height: 88,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white.withOpacity(0.04),
              border: Border.all(color: _C.gold, width: 2),
            ),
            child: Image.asset(
              "assets/images/bitxnow_logo.jpeg",
              fit: BoxFit.contain,
            ),
          ),
        ],
      ),
    );
  }

  /// Native-style progress bar + percentage, dark gold on black.
  Widget _progressBar() {
    return Column(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(6),
          child: LinearProgressIndicator(
            value: _progress > 0 ? _progress : null,
            backgroundColor: _C.border,
            valueColor: const AlwaysStoppedAnimation(_C.gold),
            minHeight: 8,
          ),
        ),
        const SizedBox(height: 10),
        Text(
          '${(_progress * 100).toStringAsFixed(0)}%',
          style: const TextStyle(
            color: _C.gold,
            fontSize: 15,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }

  Widget _errorBanner() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: _C.red.withOpacity(0.08),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _C.red.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline_rounded, color: _C.red, size: 16),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              _status,
              style: const TextStyle(
                color: Colors.redAccent,
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Shown only when the update prompt is being enforced from a cached
  /// check (the live /version call failed but we still know an update
  /// was required from the last successful check).
  Widget _cacheBanner() {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: _C.gold.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _C.gold.withOpacity(0.25)),
      ),
      child: const Row(
        children: [
          Icon(Icons.info_outline_rounded, color: _C.gold, size: 15),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              "Couldn't reach the server just now — showing the last known update info.",
              style: TextStyle(
                color: _C.gold,
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                height: 1.3,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _loadingBody() => SizedBox(
        height: 260,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _logoWithSpinner(spinning: true),
              const SizedBox(height: 18),
              const Text(
                'Checking...',
                style: TextStyle(color: _C.textSecondary, fontSize: 13),
              ),
            ],
          ),
        ),
      );

  Widget _errorBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.wifi_off_rounded, color: _C.textSecondary, size: 48),
          const SizedBox(height: 14),
          const Text(
            'Unable to Check',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Check your connection and try again.',
            textAlign: TextAlign.center,
            style: TextStyle(color: _C.textSecondary, fontSize: 12.5, height: 1.5),
          ),
          const SizedBox(height: 24),
          _gradientBtn(
            label: 'Try Again',
            icon: Icons.refresh_rounded,
            onTap: _fetchVersionInfo,
          ),
        ],
      );

  Widget _upToDateBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 76,
            height: 76,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: _C.green.withOpacity(0.08),
              border: Border.all(color: _C.green.withOpacity(0.4), width: 2),
            ),
            child: const Icon(Icons.check_rounded, color: _C.green, size: 40),
          ),
          const SizedBox(height: 18),
          const Text(
            "You're Up to Date",
            style: TextStyle(
              color: Colors.white,
              fontSize: 21,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 18),
          Row(
            children: [
              _versionChip(
                label: 'INSTALLED',
                version: _currentVer,
                color: _C.green,
              ),
              const SizedBox(width: 10),
              _versionChip(
                label: 'LATEST',
                version: _serverVer,
                color: _C.gold,
              ),
            ],
          ),
          const SizedBox(height: 18),
          _ghostBtn(label: 'Check Again', onTap: _fetchVersionInfo),
        ],
      );

  /// After a successful install: for a forced update we already navigate
  /// away automatically (see _downloadAndInstall), so this body is only
  /// really seen for optional updates that the user chose to install.
  Widget _installedBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 76,
            height: 76,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: _C.green.withOpacity(0.08),
              border: Border.all(color: _C.green.withOpacity(0.4), width: 2),
            ),
            child: const Icon(Icons.check_rounded, color: _C.green, size: 40),
          ),
          const SizedBox(height: 18),
          const Text(
            'Done',
            style: TextStyle(
              color: Colors.white,
              fontSize: 21,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 22),
          _gradientBtn(
            label: 'Okay',
            onTap: () => Navigator.pop(context),
          ),
        ],
      );

  /// Main "Android system update" style body — logo + spinner ring,
  /// progress bar and percentage, all in dark yellow / black.
  Widget _updateBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (_fromCache) _cacheBanner(),
          _logoWithSpinner(spinning: _downloading),
          const SizedBox(height: 20),
          Text(
            _downloading
                ? 'Updating...'
                : (_forceUpdt ? 'Update required' : 'New version available'),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 18),
          Row(
            children: [
              _versionChip(
                label: 'CURRENT',
                version: _currentVer,
                color: _C.textSecondary,
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Icon(Icons.arrow_forward_rounded,
                    color: _C.gold.withOpacity(0.6), size: 20),
              ),
              _versionChip(
                label: 'NEW',
                version: _serverVer,
                color: _C.gold,
              ),
            ],
          ),
          const SizedBox(height: 22),
          _divider(),
          const SizedBox(height: 20),
          if (_downloading) ...[
            _progressBar(),
            const SizedBox(height: 18),
          ],
          if (_hasError) ...[
            _errorBanner(),
            const SizedBox(height: 16),
          ],
          if (!_downloading) ...[
            _gradientBtn(
              label: _hasError ? 'Retry' : 'Update Now',
              icon: _hasError ? Icons.refresh_rounded : Icons.download_rounded,
              onTap: _downloadAndInstall,
            ),
            // "Later" is only shown for optional updates — a forced
            // update never lets the user skip or go back.
            if (!_forceUpdt) ...[
              const SizedBox(height: 10),
              _ghostBtn(label: 'Later', onTap: _dismissUpdate),
            ],
          ],
        ],
      );

  Widget _card(Widget child) => Container(
        padding: const EdgeInsets.all(26),
        decoration: BoxDecoration(
          gradient: _C.gradientCard,
          borderRadius: BorderRadius.circular(30),
          border: Border.all(color: _C.border),
          boxShadow: [
            BoxShadow(
              color: _C.orange.withOpacity(0.2),
              blurRadius: 50,
              offset: const Offset(0, 22),
            ),
          ],
        ),
        child: Stack(
          children: [
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(30),
                  gradient: _C.gradientGlow,
                ),
              ),
            ),
            child,
          ],
        ),
      );

  @override
  Widget build(BuildContext context) {
    // Forced update: back button/gesture is fully blocked, and the
    // screen does NOT hide/dismiss itself until the install completes
    // and _goToHomeOrLogin() is called.
    return PopScope(
      canPop: !_forceUpdt && !_downloading,
      onPopInvoked: (didPop) {
        if (!didPop && _forceUpdt) SystemNavigator.pop();
      },
      child: Scaffold(
        backgroundColor: _C.bg,
        body: Stack(
          children: [
            Positioned(top: -110, left: -80, child: _glowBall(260, 0.08, _C.gold)),
            Positioned(bottom: -130, right: -80, child: _glowBall(300, 0.08, _C.amber)),

            SafeArea(
              child: Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 440),
                    child: _fetching
                        ? _card(_loadingBody())
                        : FadeTransition(
                            opacity: _fade,
                            child: SlideTransition(
                              position: _slide,
                              child: _card(
                                _fetchErr
                                    ? _errorBody()
                                    : _installed
                                        ? _installedBody()
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