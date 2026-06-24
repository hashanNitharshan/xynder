import 'package:flutter/material.dart';

class BottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const BottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  static const Color bg = Color(0xff0a0a0a);
  static const Color surface = Color(0xff141414);
  static const Color border = Color(0xff2a2a2a);
  static const Color orange = Color(0xffFF4500);
  static const Color amber = Color(0xffFFB800);
  static const Color gold = Color(0xffFFD700);
  static const Color textSecondary = Color(0xff8E8E93);

  static const LinearGradient gradientAccent = LinearGradient(
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
    colors: [orange, amber, gold],
  );

  @override
  Widget build(BuildContext context) {
    final items = [
      _NavItem(icon: Icons.home_rounded, label: "Home"),
      _NavItem(icon: Icons.account_balance_wallet_rounded, label: "Request"),
      _NavItem(icon: Icons.swap_horiz_rounded, label: "Transfer"),
      _NavItem(icon: Icons.chat_rounded, label: "Chat"),
      _NavItem(icon: Icons.history_rounded, label: "History"),
      _NavItem(icon: Icons.settings_rounded, label: "Settings"),
    ];

    return SafeArea(
      top: false,
      child: Container(
        margin: const EdgeInsets.fromLTRB(12, 0, 12, 10),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 9),
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

            return Expanded(
              child: GestureDetector(
                onTap: () => onTap(index),
                behavior: HitTestBehavior.opaque,
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 220),
                  height: 56,
                  margin: const EdgeInsets.symmetric(horizontal: 2),
                  decoration: BoxDecoration(
                    gradient: selected ? gradientAccent : null,
                    color: selected ? null : Colors.transparent,
                    borderRadius: BorderRadius.circular(20),
                    border: selected
                        ? null
                        : Border.all(color: Colors.transparent),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        item.icon,
                        size: selected ? 23 : 21,
                        color: selected ? Colors.black : textSecondary,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item.label,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: selected ? Colors.black : textSecondary,
                          fontSize: 10,
                          fontWeight:
                              selected ? FontWeight.w900 : FontWeight.w600,
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
  final String label;

  const _NavItem({
    required this.icon,
    required this.label,
  });
}