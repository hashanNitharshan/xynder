import 'package:flutter/material.dart';
import '../widgets/dashboard_layout.dart';

class MerchantDashboard extends StatelessWidget {
  final Map user;

  const MerchantDashboard({super.key, required this.user});

  @override
  Widget build(BuildContext context) {
    return DashboardLayout(
      title: "Merchant Dashboard",
      role: "Merchant",
      user: user,
      menus: const [
        "Profile",
        "Merchant Requests",
        "Loan Request",
        "History",
        "Bank Details",
      ],
    );
  }
}