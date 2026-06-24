import 'package:flutter/material.dart';
import '../screens/profile_screen.dart';

class TopBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final String role;

  const TopBar({
    super.key,
    required this.title,
    required this.role,
  });

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: const Color(0xff181a20),
      title: Text(title),
      actions: [
        GestureDetector(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => ProfileScreen(role: role),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.only(right: 14),
            child: Row(
              children: [
                const CircleAvatar(
                  radius: 18,
                  backgroundImage: NetworkImage(
                    'https://i.pravatar.cc/300',
                  ),
                ),
                const SizedBox(width: 10),
                const Text(
                  "Deva",
                  style: TextStyle(fontSize: 14),
                ),
              ],
            ),
          ),
        )
      ],
    );
  }

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}