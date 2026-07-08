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
//  DESIGN TOKENS - dark black / dark yellow only.
//  Matches the app-wide Binance-style palette (bg #0B0E11,
//  bright yellow #F0B90B, dark amber #C99400, gold #D4A017,
//  grey text #848E9C).
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff0B0E11);
  static const border = Color(0xff23262B);
  static const borderFaint = Color(0xff181A1E);

  static const yellow = Color(0xffF0B90B); // bright dark-yellow
  static const amber = Color(0xffC99400); // dark amber
  static const gold = Color(0xffD4A017); // muted gold

  static const red = Color(0xffEF4444);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);
  static const textFaint = Color(0xff5C6470);

  static const gradientAccent = LinearGradient(
    colors: [Color(0xffC99400), Color(0xffF0B90B)],
  );
}

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
    with SingleTickerProviderStateMixin, WidgetsBindingObserver {
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

  // True once the Android system installer intent has been launched but
  // we haven't yet confirmed the install actually completed. OpenFile
  // only reports whether the intent opened, not whether the user
  // finished (or Android silently rejected it, e.g. on a signing-key
  // mismatch) — so we verify the real installed version once the app
  // comes back to the foreground instead of trusting that result.
  bool _awaitingInstallConfirm = false;

  late AnimationController _anim;
  late Animation<double> _fade;
  late Animation<Offset> _slide;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    _anim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 500),
    );
    _fade = CurvedAnimation(parent: _anim, curve: Curves.easeOut);
    _slide = Tween<Offset>(
      begin: const Offset(0, 0.03),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _anim, curve: Curves.easeOut));

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
    WidgetsBinding.instance.removeObserver(this);
    _anim.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    // The user just came back from the Android package-installer screen
    // (or switched away and back). If we were waiting on an install
    // confirmation, this is our chance to check whether it actually
    // took effect.
    if (state == AppLifecycleState.resumed && _awaitingInstallConfirm) {
      _verifyInstallCompleted();
    }
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
      _awaitingInstallConfirm = false;
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
        return;
      }

      // IMPORTANT: `result.type == ResultType.done` only means Android
      // successfully launched the package-installer screen — it does
      // NOT mean the install finished. The user still has to tap
      // "Install" there, and Android can silently refuse it entirely
      // (most commonly: the new APK is signed with a different key than
      // the one currently installed). Do not mark this as installed yet.
      setState(() {
        _downloading = false;
        _awaitingInstallConfirm = true;
        _status = 'Complete the install prompt, then return to the app.';
      });
    } on DioException catch (_) {
      _setError('Network error. Please try again.');
    } catch (_) {
      _setError('Something went wrong. Please try again.');
    }
  }

  /// Re-reads the actual installed app version and only NOW decides
  /// whether the update really succeeded. Called automatically when the
  /// app resumes after the installer screen, and also available as a
  /// manual "I've Installed" retry in the UI in case the lifecycle
  /// callback doesn't fire on a particular device.
  Future<void> _verifyInstallCompleted() async {
    try {
      final info = await PackageInfo.fromPlatform();
      final nowVersion = info.version;

      final stillBehind = UpdateService.isNewer(_serverVer, nowVersion);

      if (!stillBehind) {
        // The installed version now matches (or exceeds) the server
        // version — the install genuinely completed.
        await UpdateService.markVersionSkipped(_serverVer);
        if (!mounted) return;
        setState(() {
          _currentVer = nowVersion;
          _awaitingInstallConfirm = false;
          _installed = true;
        });
      } else {
        // Still on the old version — the install was cancelled, backed
        // out of, or silently rejected by Android. Say so plainly
        // instead of pretending it worked.
        if (!mounted) return;
        setState(() {
          _currentVer = nowVersion;
          _awaitingInstallConfirm = false;
          _hasError = true;
          _status =
              'Still on v$nowVersion. If the install prompt didn\'t appear or you cancelled it, tap Update Now to try again.';
        });
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _awaitingInstallConfirm = false);
    }
  }

  void _setError(String msg) => setState(() {
        _downloading = false;
        _awaitingInstallConfirm = false;
        _hasError = true;
        _status = msg;
        _progress = 0;
      });

  // ═══════════════════════════════════════════
  //  LOGO — plain by default, ringed while
  //  checking/downloading, badged when settled.
  // ═══════════════════════════════════════════
  Widget _logo({
    bool ring = false,
    double? ringValue,
    IconData? badgeIcon,
    Color? badgeColor,
  }) {
    return SizedBox(
      width: 116,
      height: 116,
      child: Stack(
        alignment: Alignment.center,
        children: [
          if (ring)
            SizedBox(
              width: 116,
              height: 116,
              child: CircularProgressIndicator(
                value: ringValue,
                strokeWidth: 2.2,
                backgroundColor: _C.border,
                valueColor: const AlwaysStoppedAnimation(_C.yellow),
              ),
            )
          else
            Container(
              width: 116,
              height: 116,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: _C.border, width: 1.4),
              ),
            ),
          Container(
            width: 92,
            height: 92,
            padding: const EdgeInsets.all(3),
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              color: _C.bg,
            ),
            child: ClipOval(
              child: Image.asset(
                "assets/images/bitxnow_logo.jpeg",
                fit: BoxFit.cover,
              ),
            ),
          ),
          if (badgeIcon != null)
            Positioned(
              right: 2,
              bottom: 2,
              child: Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _C.bg,
                  border: Border.all(color: badgeColor ?? _C.yellow, width: 1.4),
                ),
                child: Icon(badgeIcon, color: badgeColor ?? _C.yellow, size: 15),
              ),
            ),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  SLEEK LINEAR PROGRESS — logo, thin bar,
  //  percentage. Mirrors a native OS update
  //  screen instead of a boxed widget.
  // ═══════════════════════════════════════════
  Widget _progressLine() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: _progress > 0 ? _progress : null,
            minHeight: 4,
            backgroundColor: _C.border,
            valueColor: const AlwaysStoppedAnimation(_C.yellow),
          ),
        ),
        const SizedBox(height: 10),
        Align(
          alignment: Alignment.centerRight,
          child: Text(
            '${(_progress * 100).toStringAsFixed(0)}%',
            style: const TextStyle(
              color: _C.yellow,
              fontSize: 13,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.3,
            ),
          ),
        ),
      ],
    );
  }

  Widget _versionRow() {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          'v$_currentVer',
          style: const TextStyle(
            color: _C.textFaint,
            fontSize: 13,
            fontWeight: FontWeight.w700,
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10),
          child: Icon(Icons.arrow_forward_rounded,
              color: _C.yellow.withOpacity(0.7), size: 14),
        ),
        Text(
          'v$_serverVer',
          style: const TextStyle(
            color: _C.yellow,
            fontSize: 13,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }

  Widget _primaryBtn({
    required String label,
    required VoidCallback onTap,
    bool loading = false,
  }) {
    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        width: double.infinity,
        height: 52,
        decoration: BoxDecoration(
          color: _C.yellow,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Center(
          child: loading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.3,
                    color: Colors.black,
                  ),
                )
              : Text(
                  label,
                  style: const TextStyle(
                    color: Colors.black,
                    fontWeight: FontWeight.w900,
                    fontSize: 15,
                  ),
                ),
        ),
      ),
    );
  }

  Widget _textBtn(String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Text(
          label,
          style: const TextStyle(
            color: _C.textSecondary,
            fontWeight: FontWeight.w700,
            fontSize: 14,
          ),
        ),
      ),
    );
  }

  Widget _errorLine() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(top: 1),
            child: Icon(Icons.error_outline_rounded, color: _C.red, size: 15),
          ),
          const SizedBox(width: 7),
          Expanded(
            child: Text(
              _status,
              style: const TextStyle(
                color: _C.red,
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _cacheLine() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        children: [
          Icon(Icons.info_outline_rounded, color: _C.yellow.withOpacity(0.8), size: 14),
          const SizedBox(width: 7),
          Expanded(
            child: Text(
              "Couldn't reach the server — showing the last known update info.",
              style: TextStyle(
                color: _C.yellow.withOpacity(0.85),
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

  // ═══════════════════════════════════════════
  //  BODIES
  // ═══════════════════════════════════════════
  Widget _loadingBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(ring: true),
          const SizedBox(height: 26),
          const Text(
            'Checking for updates',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Please wait a moment...',
            style: TextStyle(color: _C.textSecondary, fontSize: 12.5),
          ),
        ],
      );

  Widget _errorBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(
            badgeIcon: Icons.wifi_off_rounded,
            badgeColor: _C.textSecondary,
          ),
          const SizedBox(height: 26),
          const Text(
            'Unable to Check',
            style: TextStyle(
              color: Colors.white,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Check your connection and try again.',
            textAlign: TextAlign.center,
            style: TextStyle(color: _C.textSecondary, fontSize: 12.5, height: 1.5),
          ),
          const SizedBox(height: 30),
          _primaryBtn(label: 'Try Again', onTap: _fetchVersionInfo),
        ],
      );

  Widget _upToDateBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(badgeIcon: Icons.check_rounded, badgeColor: _C.yellow),
          const SizedBox(height: 26),
          const Text(
            "You're Up to Date",
            style: TextStyle(
              color: Colors.white,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 10),
          _versionRow(),
          const SizedBox(height: 30),
          _textBtn('Check Again', _fetchVersionInfo),
        ],
      );

  Widget _installedBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(badgeIcon: Icons.check_rounded, badgeColor: _C.yellow),
          const SizedBox(height: 26),
          const Text(
            'Update Installed',
            style: TextStyle(
              color: Colors.white,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'You\'re now on v$_currentVer',
            style: const TextStyle(color: _C.textSecondary, fontSize: 12.5),
          ),
          const SizedBox(height: 30),
          _primaryBtn(label: 'Continue', onTap: () => Navigator.pop(context)),
        ],
      );

  /// Shown while we've launched the Android installer but haven't yet
  /// confirmed (via app-resume + a fresh PackageInfo read) that the
  /// install actually completed.
  Widget _awaitingConfirmBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(ring: true),
          const SizedBox(height: 26),
          const Text(
            'Waiting for Install',
            style: TextStyle(
              color: Colors.white,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _status.isEmpty
                ? 'Complete the install prompt, then return to the app.'
                : _status,
            textAlign: TextAlign.center,
            style: const TextStyle(color: _C.textSecondary, fontSize: 12.5, height: 1.5),
          ),
          const SizedBox(height: 30),
          _primaryBtn(label: "I've Installed It", onTap: _verifyInstallCompleted),
        ],
      );

  Widget _updateBody() => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          _logo(
            ring: _downloading,
            ringValue: _downloading ? (_progress > 0 ? _progress : null) : null,
          ),
          const SizedBox(height: 26),
          Text(
            _forceUpdt ? 'Update Required' : 'New Version Available',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 19,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.2,
            ),
          ),
          const SizedBox(height: 10),
          _versionRow(),
          const SizedBox(height: 28),
          if (_fromCache) _cacheLine(),
          if (_downloading) ...[
            _progressLine(),
            const SizedBox(height: 22),
          ] else ...[
            if (_hasError) _errorLine(),
            _primaryBtn(
              label: _hasError ? 'Retry' : 'Update Now',
              onTap: _downloadAndInstall,
            ),
            if (!_forceUpdt) _textBtn('Later', _dismissUpdate),
          ],
        ],
      );

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: !_forceUpdt && !_downloading && !_awaitingInstallConfirm,
      onPopInvoked: (didPop) {
        if (!didPop && _forceUpdt) SystemNavigator.pop();
      },
      child: Scaffold(
        backgroundColor: _C.bg,
        body: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 360),
                child: _fetching
                    ? _loadingBody()
                    : FadeTransition(
                        opacity: _fade,
                        child: SlideTransition(
                          position: _slide,
                          child: _fetchErr
                              ? _errorBody()
                              : _awaitingInstallConfirm
                                  ? _awaitingConfirmBody()
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
    );
  }
}