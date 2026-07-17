import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:package_info_plus/package_info_plus.dart';

import '../main.dart' show navigatorKey;
import '../services/api_service.dart';
import '../services/update_service.dart';
import 'login_screen.dart';
import 'payment_methods_screen.dart';
import 'profile_screen.dart';

class _C {
  static const bg = Color(0xff000000);
  static const surface = Color(0xff121214);
  static const border = Color(0xff252525);

  static const orange = Color(0xffFF9F2E);
  static const green = Color(0xff00C076);
  static const red = Color(0xffF6465D);

  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff77777F);
  static const textMuted = Color(0xff55555C);
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
    user = Map<String, dynamic>.from(widget.user);
    _loadVersion();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshUser();
    });
  }

  bool get isVerified {
    return user["is_verified"] == true ||
        user["is_verified"] == 1 ||
        user["is_verified"]?.toString() == "1";
  }

  String get _walletAddress {
    final walletId = user["wallet_id"]?.toString().trim() ?? "";

    if (walletId.isEmpty || walletId.toLowerCase() == "null") {
      return "Not available";
    }

    return walletId;
  }

  Future<void> _loadVersion() async {
    try {
      final info = await PackageInfo.fromPlatform();

      if (mounted) {
        setState(() {
          _appVersion = info.version;
        });
      }
    } catch (_) {}
  }

  Future<void> _refreshUser() async {
    if (widget.onProfileUpdated != null) {
      await widget.onProfileUpdated!();
    }

    try {
      final response = await ApiService.profile();

      if (response["success"] == true && mounted) {
        setState(() {
          user = Map<String, dynamic>.from(
            response["user"] ?? user,
          );
        });
      }
    } catch (_) {}
  }

  Future<void> _copyWalletAddress() async {
    if (_walletAddress == "Not available") {
      return;
    }

    await Clipboard.setData(
      ClipboardData(text: _walletAddress),
    );

    if (!mounted) return;

    _showSnack("Wallet address copied");
  }

  Future<void> _logout() async {
    await ApiService.logout();

    if (!mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (_) => const LoginScreen(),
      ),
      (_) => false,
    );
  }

  void _showSnack(
    String message, {
    bool success = true,
  }) {
    if (!mounted) return;

    final messenger = ScaffoldMessenger.of(context);

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          backgroundColor: success ? _C.green : _C.red,
          behavior: SnackBarBehavior.floating,
          margin: const EdgeInsets.fromLTRB(18, 0, 18, 18),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          content: Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 13,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      );
  }

  Future<void> _checkForUpdate() async {
    if (_checkingUpdate) return;

    setState(() {
      _checkingUpdate = true;
    });

    bool updateFound = false;

    try {
      updateFound = await UpdateService.checkForUpdate(
        navigatorKey,
        force: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _checkingUpdate = false;
        });
      }
    }

    if (!mounted) return;

    if (!updateFound) {
      _showSnack(
        "You're on the latest version ($_appVersion)",
      );
    }
  }

  Widget _topBar() {
    final canPop = Navigator.canPop(context);

    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 10, 8, 4),
      child: Row(
        children: [
          SizedBox(
            width: 44,
            child: canPop
                ? IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(
                      Icons.arrow_back_rounded,
                      color: _C.textPrimary,
                      size: 21,
                    ),
                  )
                : null,
          ),
          const Expanded(
            child: Text(
              "Settings",
              textAlign: TextAlign.center,
              style: TextStyle(
                color: _C.textPrimary,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(width: 44),
        ],
      ),
    );
  }

  Widget _profileHeader() {
    final photoUrl = ApiService.fixUrl(user["photo_url"]);
    final name = user["name"]?.toString().trim().isNotEmpty == true
        ? user["name"].toString()
        : "User";
    final email = user["email"]?.toString().trim() ?? "";

    return Padding(
      padding: const EdgeInsets.fromLTRB(18, 12, 18, 18),
      child: Column(
        children: [
          GestureDetector(
            onTap: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ProfileScreen(user: user),
                ),
              );

              await _refreshUser();
            },
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 84,
                  height: 84,
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: _C.orange,
                      width: 1.5,
                    ),
                  ),
                  child: CircleAvatar(
                    backgroundColor: _C.surface,
                    backgroundImage:
                        photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                    child: photoUrl.isEmpty
                        ? const Icon(
                            Icons.person_rounded,
                            color: _C.orange,
                            size: 38,
                          )
                        : null,
                  ),
                ),
                if (isVerified)
                  Positioned(
                    right: 0,
                    bottom: 3,
                    child: Container(
                      width: 24,
                      height: 24,
                      decoration: BoxDecoration(
                        color: _C.orange,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: _C.bg,
                          width: 2.5,
                        ),
                      ),
                      child: const Icon(
                        Icons.check_rounded,
                        color: Colors.black,
                        size: 13,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 11),
          Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: _C.textPrimary,
              fontSize: 17,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (email.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              email,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _C.textSecondary,
                fontSize: 11.5,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
          const SizedBox(height: 13),
          GestureDetector(
            onTap: _copyWalletAddress,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 11,
              ),
              decoration: BoxDecoration(
                color: _C.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: _C.border,
                  width: 0.8,
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.account_balance_wallet_outlined,
                    color: _C.orange,
                    size: 18,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          "Wallet Address",
                          style: TextStyle(
                            color: _C.textMuted,
                            fontSize: 9.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          _walletAddress,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: _C.textPrimary,
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (_walletAddress != "Not available")
                    const Icon(
                      Icons.copy_rounded,
                      color: _C.textSecondary,
                      size: 16,
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _settingsTile({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    Color iconColor = _C.textPrimary,
    Widget? trailing,
  }) {
    return InkWell(
      onTap: onTap,
      splashColor: Colors.white.withOpacity(0.025),
      highlightColor: Colors.white.withOpacity(0.015),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16),
        padding: const EdgeInsets.symmetric(vertical: 17),
        decoration: const BoxDecoration(
          border: Border(
            bottom: BorderSide(
              color: _C.border,
              width: 0.8,
            ),
          ),
        ),
        child: Row(
          children: [
            SizedBox(
              width: 28,
              child: Icon(
                icon,
                color: iconColor,
                size: 19,
              ),
            ),
            const SizedBox(width: 9),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(
                  color: _C.textPrimary,
                  fontSize: 13.5,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            trailing ??
                const Icon(
                  Icons.chevron_right_rounded,
                  color: _C.textSecondary,
                  size: 19,
                ),
          ],
        ),
      ),
    );
  }

  Widget _dialogInput(
    TextEditingController controller,
    String label, {
    bool obscure = false,
    int maxLines = 1,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: controller,
        obscureText: obscure,
        maxLines: maxLines,
        cursorColor: _C.orange,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 13,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          isDense: true,
          labelText: label,
          labelStyle: const TextStyle(
            color: _C.textSecondary,
            fontSize: 12,
          ),
          enabledBorder: const UnderlineInputBorder(
            borderSide: BorderSide(
              color: _C.border,
            ),
          ),
          focusedBorder: const UnderlineInputBorder(
            borderSide: BorderSide(
              color: _C.orange,
              width: 1.2,
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _showChangePassword() async {
    final currentController = TextEditingController();
    final newController = TextEditingController();
    final confirmController = TextEditingController();

    bool dialogLoading = false;

    await showDialog<void>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.75),
      builder: (_) {
        return StatefulBuilder(
          builder: (dialogContext, setDialogState) {
            return AlertDialog(
              backgroundColor: _C.surface,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
                side: const BorderSide(
                  color: _C.border,
                  width: 0.8,
                ),
              ),
              title: const Text(
                "Change Password",
                style: TextStyle(
                  color: _C.textPrimary,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _dialogInput(
                    currentController,
                    "Current Password",
                    obscure: true,
                  ),
                  _dialogInput(
                    newController,
                    "New Password",
                    obscure: true,
                  ),
                  _dialogInput(
                    confirmController,
                    "Confirm Password",
                    obscure: true,
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext),
                  child: const Text(
                    "Cancel",
                    style: TextStyle(
                      color: _C.textSecondary,
                    ),
                  ),
                ),
                ElevatedButton(
                  onPressed: dialogLoading
                      ? null
                      : () async {
                          setDialogState(() {
                            dialogLoading = true;
                          });

                          final response =
                              await ApiService.changePassword(
                            currentPassword:
                                currentController.text.trim(),
                            newPassword: newController.text.trim(),
                            confirmPassword:
                                confirmController.text.trim(),
                          );

                          if (dialogContext.mounted) {
                            setDialogState(() {
                              dialogLoading = false;
                            });
                          }

                          if (!dialogContext.mounted) return;

                          Navigator.pop(dialogContext);

                          _showSnack(
                            response["message"]?.toString() ??
                                (response["success"] == true
                                    ? "Password changed"
                                    : "Failed"),
                            success: response["success"] == true,
                          );
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _C.orange,
                    foregroundColor: Colors.black,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                  child: dialogLoading
                      ? const SizedBox(
                          width: 17,
                          height: 17,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.black,
                          ),
                        )
                      : const Text(
                          "Save",
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                ),
              ],
            );
          },
        );
      },
    );

    currentController.dispose();
    newController.dispose();
    confirmController.dispose();
  }

  Future<void> _showSupport() async {
    final nameController = TextEditingController(
      text: user["name"]?.toString() ?? "",
    );
    final emailController = TextEditingController(
      text: user["email"]?.toString() ?? "",
    );
    final messageController = TextEditingController();

    bool dialogLoading = false;
    List tickets = [];

    try {
      final response = await ApiService.supportTickets();

      if (response["success"] == true) {
        tickets = List.from(response["tickets"] ?? []);
      }
    } catch (_) {}

    if (!mounted) return;

    await showDialog<void>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.75),
      builder: (_) {
        return StatefulBuilder(
          builder: (dialogContext, setDialogState) {
            return AlertDialog(
              backgroundColor: _C.surface,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
                side: const BorderSide(
                  color: _C.border,
                  width: 0.8,
                ),
              ),
              title: const Text(
                "Help & Support",
                style: TextStyle(
                  color: _C.textPrimary,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
              content: SizedBox(
                width: double.maxFinite,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (tickets.isNotEmpty) ...[
                        const Text(
                          "Your Tickets",
                          style: TextStyle(
                            color: _C.textSecondary,
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ...tickets.take(3).map((ticket) {
                          final item =
                              Map<String, dynamic>.from(ticket);

                          return Container(
                            width: double.infinity,
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: _C.bg,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: _C.border,
                              ),
                            ),
                            child: Text(
                              "${item["message"]}\nStatus: ${item["status"]}",
                              style: const TextStyle(
                                color: _C.textSecondary,
                                fontSize: 11.5,
                              ),
                            ),
                          );
                        }),
                        const SizedBox(height: 10),
                      ],
                      _dialogInput(
                        nameController,
                        "Name",
                      ),
                      _dialogInput(
                        emailController,
                        "Email",
                      ),
                      _dialogInput(
                        messageController,
                        "Message",
                        maxLines: 4,
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogContext),
                  child: const Text(
                    "Cancel",
                    style: TextStyle(
                      color: _C.textSecondary,
                    ),
                  ),
                ),
                ElevatedButton(
                  onPressed: dialogLoading
                      ? null
                      : () async {
                          setDialogState(() {
                            dialogLoading = true;
                          });

                          final response =
                              await ApiService.createSupportTicket(
                            name: nameController.text.trim(),
                            email: emailController.text.trim(),
                            message: messageController.text.trim(),
                          );

                          if (dialogContext.mounted) {
                            setDialogState(() {
                              dialogLoading = false;
                            });
                          }

                          if (!dialogContext.mounted) return;

                          Navigator.pop(dialogContext);

                          _showSnack(
                            response["message"]?.toString() ??
                                (response["success"] == true
                                    ? "Ticket submitted"
                                    : "Failed"),
                            success: response["success"] == true,
                          );
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _C.orange,
                    foregroundColor: Colors.black,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                  child: dialogLoading
                      ? const SizedBox(
                          width: 17,
                          height: 17,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.black,
                          ),
                        )
                      : const Text(
                          "Submit",
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                ),
              ],
            );
          },
        );
      },
    );

    nameController.dispose();
    emailController.dispose();
    messageController.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(
            parent: BouncingScrollPhysics(),
          ),
          child: Column(
            children: [
              _topBar(),
              _profileHeader(),
              _settingsTile(
                icon: Icons.person_outline_rounded,
                label: "Personal Information",
                onTap: () async {
                  await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => ProfileScreen(user: user),
                    ),
                  );

                  await _refreshUser();
                },
              ),
              _settingsTile(
                icon: Icons.payment_outlined,
                label: "Payment Methods",
                onTap: () async {
                  await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) =>
                          PaymentMethodsScreen(user: user),
                    ),
                  );

                  await _refreshUser();
                },
              ),
              _settingsTile(
                icon: Icons.lock_outline_rounded,
                label: "Change Password",
                onTap: _showChangePassword,
              ),
              _settingsTile(
                icon: Icons.support_agent_rounded,
                label: "Help & Support",
                onTap: _showSupport,
              ),
              _settingsTile(
                icon: Icons.system_update_alt_rounded,
                label: _appVersion.isEmpty
                    ? "Check for Update"
                    : "Check for Update  v$_appVersion",
                onTap: _checkForUpdate,
                trailing: _checkingUpdate
                    ? const SizedBox(
                        width: 17,
                        height: 17,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: _C.orange,
                        ),
                      )
                    : null,
              ),
              _settingsTile(
                icon: Icons.logout_rounded,
                label: "Logout",
                iconColor: _C.red,
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
