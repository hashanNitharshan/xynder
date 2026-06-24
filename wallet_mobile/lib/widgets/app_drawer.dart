import 'package:flutter/material.dart';
import '../screens/chat_users_screen.dart';
import '../services/api_service.dart';

class AppDrawer extends StatelessWidget {
  final Map? user;

  const AppDrawer({super.key, this.user});

  @override
  Widget build(BuildContext context) {
    final name = user?["name"]?.toString() ?? "User";
    final email = user?["email"]?.toString() ?? "";
    final role = user?["role"]?.toString() ?? "";
    final photoUrl = ApiService.fixUrl(user?["photo"]);

    return Drawer(
      backgroundColor: const Color(0xff181a20),
      child: Column(
        children: [
          // ── Header ────────────────────────────────────────────
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(
              color: Color(0xff0b0e11),
              border: Border(
                bottom: BorderSide(color: Color(0xff2b3139)),
              ),
            ),
            currentAccountPicture: CircleAvatar(
              radius: 36,
              backgroundColor: const Color(0xff2b3139),
              backgroundImage: photoUrl.isNotEmpty
                  ? NetworkImage(photoUrl)
                  : null,
              child: photoUrl.isEmpty
                  ? Text(
                      name.isNotEmpty ? name[0].toUpperCase() : "U",
                      style: const TextStyle(
                        color: Color(0xfff0b90b),
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                    )
                  : null,
            ),
            accountName: Text(
              name,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
            accountEmail: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  email,
                  style: const TextStyle(
                      color: Colors.white54, fontSize: 12),
                ),
                if (role.isNotEmpty)
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xfff0b90b).withOpacity(0.15),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                          color: const Color(0xfff0b90b)
                              .withOpacity(0.4)),
                    ),
                    child: Text(
                      role.toUpperCase(),
                      style: const TextStyle(
                        color: Color(0xfff0b90b),
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // ── Menu Items ────────────────────────────────────────
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                // Chats
                _DrawerTile(
                  icon: Icons.chat_rounded,
                  label: "Transaction Chats",
                  onTap: () {
                    Navigator.pop(context);
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const ChatUsersScreen(),
                      ),
                    );
                  },
                ),

                const _DrawerDivider(),

                // Profile
                _DrawerTile(
                  icon: Icons.person_rounded,
                  label: "Profile",
                  onTap: () => Navigator.pop(context),
                ),

                // Wallet
                _DrawerTile(
                  icon: Icons.account_balance_wallet_rounded,
                  label: "Wallet",
                  onTap: () => Navigator.pop(context),
                ),

                // Settings
                _DrawerTile(
                  icon: Icons.settings_rounded,
                  label: "Settings",
                  onTap: () => Navigator.pop(context),
                ),
              ],
            ),
          ),

          // ── Footer ────────────────────────────────────────────
          const Divider(color: Color(0xff2b3139), height: 1),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                const Icon(Icons.info_outline_rounded,
                    color: Colors.white24, size: 16),
                const SizedBox(width: 8),
                Text(
                  role.isNotEmpty
                      ? "Logged in as ${role[0].toUpperCase()}${role.substring(1)}"
                      : "Xynder Wallet",
                  style: const TextStyle(
                      color: Colors.white24, fontSize: 11),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Helper Widgets ───────────────────────────────────────────────────────

class _DrawerTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback? onTap;

  const _DrawerTile({
    required this.icon,
    required this.label,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: Colors.white70, size: 22),
      title: Text(
        label,
        style: const TextStyle(color: Colors.white, fontSize: 14),
      ),
      onTap: onTap,
      dense: true,
      horizontalTitleGap: 8,
      shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10)),
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
    );
  }
}

class _DrawerDivider extends StatelessWidget {
  const _DrawerDivider();

  @override
  Widget build(BuildContext context) {
    return const Divider(
      color: Color(0xff2b3139),
      height: 16,
      indent: 16,
      endIndent: 16,
    );
  }
}