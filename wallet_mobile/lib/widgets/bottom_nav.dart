import 'package:flutter/material.dart';
import '../screens/update_screen.dart'; // for nav tap → UpdateScreen

class BottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const BottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  static const Color bg            = Color(0xff0a0a0a);
  static const Color surface       = Color(0xff141414);
  static const Color border        = Color(0xff2a2a2a);
  static const Color orange        = Color(0xffFF4500);
  static const Color amber         = Color(0xffFFB800);
  static const Color gold          = Color(0xffFFD700);
  static const Color textSecondary = Color(0xff8E8E93);

  static const LinearGradient gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [orange, amber, gold],
  );

  // ── Static helper: use this in every dashboard's onTap handler ────────────
  //
  //   BottomNav.handleTap(context, index, setState: (i) => setState(() => _tab = i));
  //
  static void handleTap(
    BuildContext context,
    int index, {
    required void Function(int) setState,
  }) {
    if (index == 6) {
      // "Update" tab → full-screen page (self-loads version data)
      Navigator.push(
        context,
        MaterialPageRoute(
          fullscreenDialog: true,
          builder: (_) => const UpdateScreen(),
        ),
      );
      return; // don't change tab index — stays on current tab after pop
    }
    setState(index);
  }

  @override
  Widget build(BuildContext context) {
    // 7 items — icon 22→20, font 10→9, margin 2→1 to keep everything compact
    final items = [
      _NavItem(icon: Icons.home_rounded,                    label: 'Home'),
      _NavItem(icon: Icons.account_balance_wallet_rounded,  label: 'Request'),
      _NavItem(icon: Icons.swap_horiz_rounded,              label: 'Transfer'),
      _NavItem(icon: Icons.chat_rounded,                    label: 'Chat'),
      _NavItem(icon: Icons.history_rounded,                 label: 'History'),
      _NavItem(icon: Icons.settings_rounded,                label: 'Settings'),
      _NavItem(icon: Icons.system_update_alt_rounded,       label: 'Update'),  // ← NEW
    ];

    return SafeArea(
      top: false,
      child: Container(
        margin: const EdgeInsets.fromLTRB(12, 0, 12, 10),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 9),
        decoration: BoxDecoration(
          color: surface,
          borderRadius: BorderRadius.circular(26),
          border: Border.all(color: border),
          boxShadow: [
            BoxShadow(
              color: orange.withOpacity(0.16),
              blurRadius: 30,
              offset: const Offset(0, 12),
            ),
          ],
        ),
        child: Row(
          children: List.generate(items.length, (index) {
            final selected = currentIndex == index;
            final item     = items[index];
            // "Update" tab uses amber glow colour when selected, not gradient,
            // to distinguish it as a utility action rather than a main section.
            final isUpdate = index == 6;

            return Expanded(
              child: GestureDetector(
                onTap: () => onTap(index),
                behavior: HitTestBehavior.opaque,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 220),
                  height: 54,
                  margin: const EdgeInsets.symmetric(horizontal: 1),
                  decoration: BoxDecoration(
                    gradient: selected && !isUpdate ? gradientAccent : null,
                    color: selected && isUpdate
                        ? amber.withOpacity(0.15)
                        : selected
                            ? null
                            : Colors.transparent,
                    borderRadius: BorderRadius.circular(18),
                    border: selected && isUpdate
                        ? Border.all(color: amber.withOpacity(0.4))
                        : Border.all(color: Colors.transparent),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        item.icon,
                        size: selected ? 22 : 20,
                        color: selected
                            ? (isUpdate ? amber : Colors.black)
                            : textSecondary,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        item.label,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: selected
                              ? (isUpdate ? amber : Colors.black)
                              : textSecondary,
                          fontSize: 9,
                          fontWeight: selected
                              ? FontWeight.w900
                              : FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }
}

class _NavItem {
  final IconData icon;
  final String   label;
  const _NavItem({required this.icon, required this.label});
}