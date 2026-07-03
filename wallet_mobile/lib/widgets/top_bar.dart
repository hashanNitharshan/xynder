import 'package:flutter/material.dart';

import '../screens/login_screen.dart';
import '../screens/profile_screen.dart';
import '../services/api_service.dart';
import '../utils/page_transitions.dart';

class TopBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final Map user;
  final String role;
  final bool showBack;
  final bool backToRoot;
  final VoidCallback? onBack;
  final List<Widget>? extraActions;

  const TopBar({
    super.key,
    required this.title,
    this.user = const {},
    this.role = "user",
    this.showBack = true,
    this.backToRoot = false,
    this.onBack,
    this.extraActions,
  });

  static const bg = Color(0xff0B0E11);
  static const surface = Color(0xff181A20);
  static const surfaceAlt = Color(0xff1E2329);
  static const border = Color(0xff2B3139);
  static const gold = Color(0xffF0B90B);
  static const red = Color(0xffF6465D);
  static const textPrimary = Colors.white;
  static const textSecondary = Color(0xff848E9C);

  void _handleBack(BuildContext context) {
    if (onBack != null) {
      onBack!();
      return;
    }

    if (backToRoot) {
      Navigator.popUntil(context, (route) => route.isFirst);
    } else if (Navigator.canPop(context)) {
      Navigator.pop(context);
    }
  }

  Future<void> _logout(BuildContext context) async {
    await ApiService.logout();

    if (!context.mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (_) => false,
    );
  }

  bool _isVerified() {
    return user["is_verified"] == true ||
        user["is_verified"] == 1 ||
        user["is_verified"]?.toString() == "1";
  }

  @override
  Widget build(BuildContext context) {
    final canGoBack = Navigator.canPop(context);
    final name = user["name"]?.toString() ?? "User";
    final email = user["email"]?.toString() ?? "";
    final photoUrl = ApiService.fixUrl(user["photo_url"] ?? user["photo"]);
    final verified = _isVerified();

    return AppBar(
      automaticallyImplyLeading: false,
      backgroundColor: bg,
      elevation: 0,
      toolbarHeight: 68,
      titleSpacing: 0,
      title: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          children: [
            if (showBack && canGoBack) ...[
              InkWell(
                borderRadius: BorderRadius.circular(12),
                onTap: () => _handleBack(context),
                child: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: surface,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: border),
                  ),
                  child: const Icon(
                    Icons.arrow_back_ios_new_rounded,
                    color: Colors.white,
                    size: 16,
                  ),
                ),
              ),
              const SizedBox(width: 12),
            ],

            Expanded(
              child: Text(
                title,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: textPrimary,
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),

            if (extraActions != null) ...extraActions!,

            PopupMenuButton<String>(
              color: surfaceAlt,
              elevation: 12,
              offset: const Offset(0, 48),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: const BorderSide(color: border),
              ),
              onSelected: (v) {
                if (v == "profile") {
                  Navigator.push(
                    context,
                    XRoute.slideRight(ProfileScreen(user: user)),
                  );
                }

                if (v == "logout") {
                  _logout(context);
                }
              },
              itemBuilder: (_) => [
                PopupMenuItem(
                  enabled: false,
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 18,
                        backgroundColor: surface,
                        backgroundImage:
                            photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                        child: photoUrl.isEmpty
                            ? const Icon(
                                Icons.person,
                                color: Colors.white54,
                                size: 18,
                              )
                            : null,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              name,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            if (email.isNotEmpty)
                              Text(
                                email,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: textSecondary,
                                  fontSize: 11,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const PopupMenuDivider(),
                const PopupMenuItem(
                  value: "profile",
                  child: Row(
                    children: [
                      Icon(Icons.person_rounded, color: gold, size: 20),
                      SizedBox(width: 10),
                      Text("Profile", style: TextStyle(color: Colors.white)),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: "logout",
                  child: Row(
                    children: [
                      Icon(Icons.logout_rounded, color: red, size: 20),
                      SizedBox(width: 10),
                      Text("Logout", style: TextStyle(color: red)),
                    ],
                  ),
                ),
              ],
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    padding: const EdgeInsets.all(2),
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: gold, width: 1.8),
                    ),
                    child: CircleAvatar(
                      radius: 19,
                      backgroundColor: surface,
                      backgroundImage:
                          photoUrl.isNotEmpty ? NetworkImage(photoUrl) : null,
                      child: photoUrl.isEmpty
                          ? const Icon(
                              Icons.person,
                              color: Colors.white54,
                              size: 20,
                            )
                          : null,
                    ),
                  ),
                  if (verified)
                    Positioned(
                      right: -1,
                      bottom: -1,
                      child: Container(
                        padding: const EdgeInsets.all(2),
                        decoration: const BoxDecoration(
                          color: bg,
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.verified_rounded,
                          color: gold,
                          size: 14,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(68);
}