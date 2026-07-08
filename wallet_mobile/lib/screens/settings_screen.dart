import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';

import '../services/api_service.dart';
import '../services/update_service.dart';
import 'profile_screen.dart';
import 'login_screen.dart';
import 'payment_methods_screen.dart';
import '../main.dart' show navigatorKey;

// ─────────────────────────────────────────────────────────────
//  DESIGN TOKENS - SAME STYLE AS OTHER SCREENS (Dark Yellow)
// ─────────────────────────────────────────────────────────────
class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff0D0D0D);
  static const surfaceAlt = Color(0xff171717);

  static const border = Color(0xff2E2E2E);

  // Theme (Dark Yellow / Goldenrod)
  static const orange = Color(0xffB8860B); // dark goldenrod
  static const amber = Color(0xff9A6B00); // deep amber
  static const gold = Color(0xffD4A017); // muted gold highlight

  static const green = Color(0xff22C55E);
  static const red = Color(0xffEF4444);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xffA3A3A3);

  static const gradientCard = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [
      Color(0xff050505),
      Color(0xff111111),
      Color(0xff1A1500),
    ],
  );
}

class SettingsScreen extends StatefulWidget {
  final Map user;
  final Future<void> Function()? onProfileUpdated;

  const SettingsScreen({
    super.key,
    required this.user,
    this.onProfileUpdated,
  });

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  late Map user;

  String _appVersion = "";
  bool _checkingUpdate = false;

  @override
  void initState() {
    super.initState();
    user = widget.user;
    _loadVersion();
  }

  Future<void> _loadVersion() async {
    try {
      final info = await PackageInfo.fromPlatform();
      if (mounted) {
        setState(() => _appVersion = info.version);
      }
    } catch (_) {}
  }

  bool get isVerified =>
      user["is_verified"] == true ||
      user["is_verified"] == 1 ||
      user["is_verified"]?.toString() == "1";

  String get _role => user["role"]?.toString() ?? "user";

  Future<void> _refreshUser() async {
    if (widget.onProfileUpdated != null) {
      await widget.onProfileUpdated!();
    }

    try {
      final pd = await ApiService.profile();
      if (pd["success"] == true && mounted) {
        setState(() {
          user = Map<String, dynamic>.from(pd["user"]);
        });
      }
    } catch (_) {}
  }

  Future<void> _logout() async {
    await ApiService.logout();
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  void _showSnack(String msg, {bool success = true}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      backgroundColor: success ? _C.amber : _C.red,
      content: Text(
        msg,
        style: const TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w700,
        ),
      ),
    ));
  }

  // ═══════════════════════════════════════════
  //  CHECK FOR UPDATE
  // ═══════════════════════════════════════════
  Future<void> _checkForUpdate() async {
    if (_checkingUpdate) return;
    setState(() => _checkingUpdate = true);

    final beforeVersion = _appVersion;

    try {
      await UpdateService.checkForUpdate(navigatorKey);
    } finally {
      if (mounted) setState(() => _checkingUpdate = false);
    }

    // If UpdateService didn't push the UpdateScreen (i.e. already latest
    // or skipped), give the user feedback instead of silence.
    if (!mounted) return;
    if (beforeVersion == _appVersion) {
      _showSnack("You're on the latest version ($_appVersion)");
    }
  }

  // ═══════════════════════════════════════════
  //  PROFILE CARD
  // ═══════════════════════════════════════════
  Widget _profileCard() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);
    final name = user["name"]?.toString() ?? "User";
    final email = user["email"]?.toString() ?? "";
    final phone = user["phone"]?.toString() ?? "";

    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => ProfileScreen(user: user)),
      ).then((_) => _refreshUser()),
      child: Container(
        margin: const EdgeInsets.fromLTRB(20, 18, 20, 16),
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: _C.gradientCard,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: const Color(0xff3A2E00)),
        ),
        child: Row(
          children: [
            Container(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: _C.orange, width: 2),
              ),
              child: CircleAvatar(
                radius: 30,
                backgroundColor: _C.surfaceAlt,
                backgroundImage:
                    photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                child: photoUrl.isEmpty
                    ? const Icon(Icons.person, color: Colors.white54, size: 28)
                    : null,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name,
                      style: const TextStyle(
                          color: _C.textPrimary,
                          fontSize: 17,
                          fontWeight: FontWeight.w800)),
                  if (phone.isNotEmpty)
                    Text(phone,
                        style: const TextStyle(
                            color: _C.textSecondary, fontSize: 12)),
                  if (email.isNotEmpty)
                    Text(email,
                        style: const TextStyle(
                            color: _C.textSecondary, fontSize: 12)),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(
                        isVerified
                            ? Icons.verified_rounded
                            : Icons.warning_amber_rounded,
                        color: isVerified ? _C.green : _C.gold,
                        size: 14,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isVerified ? "Verified" : "Unverified",
                        style: TextStyle(
                          color: isVerified ? _C.green : _C.gold,
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded,
                color: _C.textSecondary, size: 14),
          ],
        ),
      ),
    );
  }

  // ═══════════════════════════════════════════
  //  SETTINGS TILE
  // ═══════════════════════════════════════════
  Widget _settingsTile({
    required IconData icon,
    required String label,
    required String sub,
    required VoidCallback onTap,
    Color color = _C.orange,
    Widget? trailing,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.fromLTRB(20, 0, 20, 10),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: _C.surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: _C.border),
        ),
        child: Row(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(13),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label,
                      style: const TextStyle(
                          color: _C.textPrimary,
                          fontWeight: FontWeight.w700,
                          fontSize: 14)),
                  Text(sub,
                      style: const TextStyle(
                          color: _C.textSecondary, fontSize: 12)),
                ],
              ),
            ),
            trailing ??
                const Icon(Icons.arrow_forward_ios_rounded,
                    color: _C.textSecondary, size: 14),
          ],
        ),
      ),
    );
  }

  Widget _dialogInput(TextEditingController ctrl, String label,
      {bool obscure = false, int maxLines = 1}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: ctrl,
        obscureText: obscure,
        maxLines: maxLines,
        style: const TextStyle(color: Colors.white),
        decoration: InputDecoration(
          labelText: label,
          labelStyle: const TextStyle(color: _C.textSecondary),
          filled: true,
          fillColor: _C.bg,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _C.border),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _C.orange),
          ),
        ),
      ),
    );
  }

  Future<void> _showChangePassword() async {
    final curr = TextEditingController();
    final nw = TextEditingController();
    final cf = TextEditingController();
    bool loading = false;
    await showDialog(
      context: context,
      builder: (_) => StatefulBuilder(builder: (ctx, set) {
        return AlertDialog(
          backgroundColor: _C.surfaceAlt,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Text("Change Password",
              style: TextStyle(color: Colors.white)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _dialogInput(curr, "Current Password", obscure: true),
              _dialogInput(nw, "New Password", obscure: true),
              _dialogInput(cf, "Confirm Password", obscure: true),
            ],
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text("Cancel")),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _C.orange,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: loading
                  ? null
                  : () async {
                      set(() => loading = true);
                      final res = await ApiService.changePassword(
                        currentPassword: curr.text.trim(),
                        newPassword: nw.text.trim(),
                        confirmPassword: cf.text.trim(),
                      );
                      set(() => loading = false);
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      _showSnack(
                        res["message"] ??
                            (res["success"] == true
                                ? "Password changed"
                                : "Failed"),
                        success: res["success"] == true,
                      );
                    },
              child: loading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Text("Save"),
            ),
          ],
        );
      }),
    );
  }

  Future<void> _showSupport() async {
    final nameCtrl =
        TextEditingController(text: user["name"]?.toString() ?? "");
    final emailCtrl =
        TextEditingController(text: user["email"]?.toString() ?? "");
    final msgCtrl = TextEditingController();
    bool loading = false;
    List tickets = [];
    final td = await ApiService.supportTickets();
    if (td["success"] == true) tickets = td["tickets"] ?? [];

    if (!mounted) return;

    await showDialog(
      context: context,
      builder: (_) => StatefulBuilder(builder: (ctx, set) {
        return AlertDialog(
          backgroundColor: _C.surfaceAlt,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Text("Help & Support",
              style: TextStyle(color: Colors.white)),
          content: SizedBox(
            width: double.maxFinite,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (tickets.isNotEmpty) ...[
                    const Text("Your Tickets",
                        style: TextStyle(
                            color: Colors.white70,
                            fontWeight: FontWeight.w700)),
                    const SizedBox(height: 8),
                    ...tickets.take(3).map((t) {
                      final ti = Map<String, dynamic>.from(t);
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: _C.bg,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: _C.border),
                        ),
                        child: Text(
                          "${ti["message"]}\nStatus: ${ti["status"]}",
                          style: const TextStyle(
                              color: Colors.white70, fontSize: 12),
                        ),
                      );
                    }),
                    const SizedBox(height: 12),
                  ],
                  _dialogInput(nameCtrl, "Name"),
                  _dialogInput(emailCtrl, "Email"),
                  _dialogInput(msgCtrl, "Message", maxLines: 4),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text("Cancel")),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: _C.orange,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: loading
                  ? null
                  : () async {
                      set(() => loading = true);
                      final res = await ApiService.createSupportTicket(
                        name: nameCtrl.text.trim(),
                        email: emailCtrl.text.trim(),
                        message: msgCtrl.text.trim(),
                      );
                      set(() => loading = false);
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      _showSnack(
                        res["message"] ??
                            (res["success"] == true
                                ? "Ticket submitted"
                                : "Failed"),
                        success: res["success"] == true,
                      );
                    },
              child: loading
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Text("Submit"),
            ),
          ],
        );
      }),
    );
  }

  // ═══════════════════════════════════════════
  //  BUILD
  // ═══════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              _profileCard(),
              _settingsTile(
  icon: Icons.payment_rounded,
  label: "Payment Methods",
  sub: "Bank accounts and UPI details",
  onTap: () => Navigator.push(
    context,
    MaterialPageRoute(builder: (_) => PaymentMethodsScreen(user: user)),
  ).then((_) => _refreshUser()),
),
              _settingsTile(
                icon: Icons.lock_reset_rounded,
                label: "Change Password",
                sub: "Update your account password",
                onTap: _showChangePassword,
              ),
              _settingsTile(
                icon: Icons.support_agent_rounded,
                label: "Help & Support",
                sub: "Raise a support ticket",
                onTap: _showSupport,
              ),
              _settingsTile(
                icon: Icons.system_update_rounded,
                label: "Check for Update",
                sub: _appVersion.isEmpty
                    ? "Loading version..."
                    : "Current version: $_appVersion",
                color: _C.gold,
                onTap: _checkForUpdate,
                trailing: _checkingUpdate
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: _C.gold,
                        ),
                      )
                    : const Icon(Icons.arrow_forward_ios_rounded,
                        color: _C.textSecondary, size: 14),
              ),
              _settingsTile(
                icon: Icons.logout_rounded,
                label: "Logout",
                sub: "Sign out from your account",
                color: _C.red,
                onTap: _logout,
              ),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }
}