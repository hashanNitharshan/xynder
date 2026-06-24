import 'package:flutter/material.dart';
import '../widgets/dashboard_layout.dart';

class ClientDashboard extends StatelessWidget {
  final Map user;

  const ClientDashboard({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    return DashboardLayout(
      title: "Client Dashboard",
      role: "Client",
      user: user,
      menus: const [],
    );
  }
}