import 'package:flutter/material.dart';
import '../screens/update_screen.dart';

/// Bottom navigation.
/// 0 = Home, 1 = P2P, 2 = Transfer, 3 = Trade (live chart), 4 = Settings
class BottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const BottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  static const Color surface = Color(0xff181A20);
  static const Color border = Color(0xff2B3139);
  static const Color yellow = Color(0xffF0B90B);
  static const Color muted = Color(0xff848E9C);

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
    const items = [
      Icons.home_rounded,
      Icons.account_balance_wallet_rounded,
      Icons.swap_horiz_rounded,
      Icons.candlestick_chart_rounded,
      Icons.settings_rounded,
    ];

    return SafeArea(
      top: false,
      child: Container(
        margin: const EdgeInsets.fromLTRB(14, 0, 14, 12),
        height: 68,
        decoration: BoxDecoration(
          color: surface,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: .45),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Row(
          children: List.generate(items.length, (index) {
            final selected = currentIndex == index;

            return Expanded(
              child: InkWell(
                borderRadius: BorderRadius.circular(18),
                splashColor: Colors.transparent,
                highlightColor: Colors.transparent,
                onTap: () => onTap(index),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 220),
                  curve: Curves.easeOut,
                  margin: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: selected
                        ? yellow.withValues(alpha: .15)
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Center(
                    child: Icon(
                      items[index],
                      size: selected ? 28 : 23,
                      color: selected ? yellow : muted,
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