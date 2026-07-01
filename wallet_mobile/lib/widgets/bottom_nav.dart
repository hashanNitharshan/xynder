import 'package:flutter/material.dart';
import '../screens/update_screen.dart';

class BottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const BottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  static const Color surface = Color(0xff141414);
  static const Color border = Color(0xff2a2a2a);
  static const Color orange = Color(0xffFF4500);
  static const Color amber = Color(0xffFFB800);
  static const Color gold = Color(0xffFFD700);
  static const Color textSecondary = Color(0xff8E8E93);

  static const LinearGradient gradientAccent = LinearGradient(
    colors: [orange, amber, gold],
  );

  static void handleTap(
    BuildContext context,
    int index, {
    required void Function(int) setState,
  }) {
    if (index == 5) {
      Navigator.push(
        context,
        MaterialPageRoute(
          fullscreenDialog: true,
          builder: (_) => const UpdateScreen(),
        ),
      );
      return;
    }
    setState(index);
  }

  @override
  Widget build(BuildContext context) {
    final items = [
      _NavItem(icon: Icons.home_rounded, label: 'Home'),
      _NavItem(icon: Icons.account_balance_wallet_rounded, label: 'P2P'),
      _NavItem(icon: Icons.swap_horiz_rounded, label: 'Transfer'),
      _NavItem(icon: Icons.history_rounded, label: 'History'),
      _NavItem(icon: Icons.settings_rounded, label: 'Settings'),
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
            final item = items[index];
            final isUpdate = index == 5;

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
                  child: Center(
                    child: Icon(
                      item.icon,
                      size: selected ? 26 : 23,
                      color: selected
                          ? (isUpdate ? amber : Colors.black)
                          : textSecondary,
                    ),
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
  final String label;
  const _NavItem({required this.icon, required this.label});
}