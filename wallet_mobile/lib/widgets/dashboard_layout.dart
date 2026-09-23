import 'dart:async';

import 'package:flutter/material.dart';

import '../screens/history_screen.dart';
import '../screens/login_screen.dart';
import '../screens/merchant_requests_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/request_screen.dart';
import '../screens/settings_screen.dart';
import '../screens/wallet_transfer_screen.dart';
import '../services/api_service.dart';
import '../utils/page_transitions.dart';
import '../widgets/bottom_nav.dart';

class _AppColors {
  static const background = Color(0xFF000000);
  static const surface = Color(0xFF1C1C22);
  static const surfaceLight = Color(0xFF24242B);
  static const border = Color(0xFF2B2B31);

  static const primaryText = Colors.white;
  static const secondaryText = Color(0xFF85858D);
  static const mutedText = Color(0xFF5F5F67);

  static const orange = Color(0xFFFF8A24);
  static const green = Color(0xFF19C784);
  static const red = Color(0xFFF6465D);
}

class DashboardLayout extends StatefulWidget {
  final String title;
  final String role;
  final Map user;
  final List<String> menus;

  const DashboardLayout({
    super.key,
    required this.title,
    required this.role,
    required this.user,
    required this.menus,
  });

  @override
  State<DashboardLayout> createState() => _DashboardLayoutState();
}

class _DashboardLayoutState extends State<DashboardLayout> with WidgetsBindingObserver {
  /// Online heartbeat. The server marks a user offline after about
  /// 2 minutes with no heartbeat, so 45 seconds keeps the user online.
  static const Duration _pingInterval = Duration(seconds: 45);

  late Map user;

  int currentIndex = 0;
  String historyInitialType = 'all';
  bool balanceHidden = false;
  bool loading = true;

  Timer? _pingTimer;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    user = Map<String, dynamic>.from(widget.user);
    refreshProfile();
    _startPing();
  }

  @override
  void dispose() {
    _stopPing();
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      refreshProfile();
      _startPing();
    }

    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.detached) {
      _stopPing();
    }
  }

  void _startPing() {
    _pingTimer?.cancel();
    _pingTimer = Timer.periodic(_pingInterval, (_) {
      ApiService.ping();
    });
  }

  void _stopPing() {
    _pingTimer?.cancel();
    _pingTimer = null;
  }

  double _toDouble(dynamic value) {
    return double.tryParse(
          value?.toString().replaceAll(',', '').trim() ?? '0',
        ) ??
        0;
  }

  String _money(dynamic value) {
    final amount = _toDouble(value);
    final parts = amount.toStringAsFixed(2).split('.');

    final whole = parts.first.replaceAllMapped(
      RegExp(r'\B(?=(\d{3})+(?!\d))'),
      (_) => ',',
    );

    return '$whole.${parts.last}';
  }

  bool get isVerified {
    return user['is_verified'] == true ||
        user['is_verified'] == 1 ||
        user['is_verified']?.toString() == '1';
  }

  Future<void> refreshProfile() async {
    try {
      final response = await ApiService.profile();

      if (!mounted) return;

      if (response['success'] == true && response['user'] is Map) {
        final freshUser = Map<String, dynamic>.from(
          response['user'] as Map,
        );

        setState(() {
          user = freshUser;
          loading = false;
        });
      } else {
        setState(() {
          loading = false;
        });
      }

      await ApiService.ping();
    } catch (error) {
      debugPrint('Dashboard profile error: $error');

      if (mounted) {
        setState(() {
          loading = false;
        });
      }
    }
  }

  Future<void> logout() async {
    _stopPing();
    await ApiService.logout();

    if (!mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      XRoute.fade(const LoginScreen()),
      (_) => false,
    );
  }

  void _openP2PRequestHistory() {
    setState(() {
      historyInitialType = 'request';
      currentIndex = 3;
    });

    refreshProfile();
  }

  void _openTransferHistory() {
    setState(() {
      historyInitialType = 'transfer';
      currentIndex = 3;
    });

    refreshProfile();
  }

  void _switchTab(int index) {
    setState(() {
      currentIndex = index;

      if (index == 3) {
        historyInitialType = 'all';
      }
    });

    // Refresh the authenticated user whenever a page that displays wallet
    // information is opened. This keeps wallet_id and balance synchronized.
    if (index == 0 || index == 2 || index == 3 || index == 4) {
      refreshProfile();
    }
  }

  Widget _topBar() {
    final photoUrl = ApiService.fixUrl(user['photo_url']);
    final name = user['name']?.toString().trim().isNotEmpty == true
        ? user['name'].toString()
        : 'User';
    final email = user['email']?.toString().trim() ?? '';

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 6),
      child: Row(
        children: [
          PopupMenuButton<String>(
            color: _AppColors.surface,
            elevation: 12,
            offset: const Offset(0, 48),
            constraints: const BoxConstraints(
              minWidth: 240,
              maxWidth: 270,
            ),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(
                color: _AppColors.border,
                width: 0.8,
              ),
            ),
            onSelected: (value) {
              if (value == 'profile') {
                Navigator.push(
                  context,
                  XRoute.slideRight(
                    ProfileScreen(user: user),
                  ),
                ).then((_) => refreshProfile());
              }

              if (value == 'logout') {
                logout();
              }
            },
            itemBuilder: (_) => [
              PopupMenuItem<String>(
                enabled: false,
                height: 74,
                padding: const EdgeInsets.fromLTRB(
                  14,
                  10,
                  14,
                  10,
                ),
                child: Row(
                  children: [
                    Stack(
                      clipBehavior: Clip.none,
                      children: [
                        CircleAvatar(
                          radius: 23,
                          backgroundColor: _AppColors.surfaceLight,
                          backgroundImage: photoUrl.isNotEmpty
                              ? NetworkImage(photoUrl)
                              : null,
                          child: photoUrl.isEmpty
                              ? const Icon(
                                  Icons.person_rounded,
                                  color: Colors.white70,
                                  size: 23,
                                )
                              : null,
                        ),
                        if (isVerified)
                          Positioned(
                            right: -2,
                            bottom: -2,
                            child: Container(
                              width: 17,
                              height: 17,
                              decoration: BoxDecoration(
                                color: _AppColors.green,
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: _AppColors.surface,
                                  width: 2,
                                ),
                              ),
                              child: const Icon(
                                Icons.check_rounded,
                                color: Colors.white,
                                size: 10,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(width: 11),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment:
                            CrossAxisAlignment.start,
                        children: [
                          Text(
                            name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: _AppColors.primaryText,
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          if (email.isNotEmpty) ...[
                            const SizedBox(height: 3),
                            Text(
                              email,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: _AppColors.secondaryText,
                                fontSize: 10.5,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(
                                isVerified
                                    ? Icons.verified_rounded
                                    : Icons.warning_amber_rounded,
                                color: isVerified
                                    ? _AppColors.green
                                    : _AppColors.red,
                                size: 13,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                isVerified
                                    ? 'Verified'
                                    : 'Unverified',
                                style: TextStyle(
                                  color: isVerified
                                      ? _AppColors.green
                                      : _AppColors.red,
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const PopupMenuDivider(height: 1),
              const PopupMenuItem<String>(
                value: 'profile',
                height: 48,
                child: Row(
                  children: [
                    Icon(
                      Icons.person_outline_rounded,
                      color: _AppColors.primaryText,
                      size: 19,
                    ),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Profile',
                        style: TextStyle(
                          color: _AppColors.primaryText,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    Icon(
                      Icons.chevron_right_rounded,
                      color: _AppColors.secondaryText,
                      size: 18,
                    ),
                  ],
                ),
              ),
              const PopupMenuDivider(height: 1),
              const PopupMenuItem<String>(
                value: 'logout',
                height: 48,
                child: Row(
                  children: [
                    Icon(
                      Icons.logout_rounded,
                      color: _AppColors.red,
                      size: 19,
                    ),
                    SizedBox(width: 12),
                    Text(
                      'Logout',
                      style: TextStyle(
                        color: _AppColors.red,
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 42,
                  height: 42,
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: isVerified
                          ? _AppColors.green
                          : _AppColors.border,
                      width: 1.3,
                    ),
                  ),
                  child: CircleAvatar(
                    backgroundColor: _AppColors.surface,
                    backgroundImage: photoUrl.isNotEmpty
                        ? NetworkImage(photoUrl)
                        : null,
                    child: photoUrl.isEmpty
                        ? const Icon(
                            Icons.person_rounded,
                            color: Colors.white70,
                            size: 19,
                          )
                        : null,
                  ),
                ),
                Positioned(
                  right: -2,
                  bottom: -2,
                  child: Container(
                    width: 16,
                    height: 16,
                    decoration: BoxDecoration(
                      color: isVerified
                          ? _AppColors.green
                          : _AppColors.red,
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: _AppColors.background,
                        width: 2,
                      ),
                    ),
                    child: Icon(
                      isVerified
                          ? Icons.check_rounded
                          : Icons.close_rounded,
                      color: Colors.white,
                      size: 9,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Spacer(),
          const Text(
            'Wallet',
            style: TextStyle(
              color: _AppColors.primaryText,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          const Spacer(),
          Stack(
            clipBehavior: Clip.none,
            children: [
              const Icon(
                Icons.notifications_none_rounded,
                color: Colors.white,
                size: 21,
              ),
              Positioned(
                right: -7,
                top: -5,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 4,
                    vertical: 1,
                  ),
                  decoration: BoxDecoration(
                    color: _AppColors.red,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    '9+',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 7,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _searchBar() {
    return Container(
      height: 34,
      margin: const EdgeInsets.fromLTRB(14, 3, 14, 0),
      padding: const EdgeInsets.symmetric(horizontal: 11),
      decoration: BoxDecoration(
        color: _AppColors.surface,
        borderRadius: BorderRadius.circular(18),
      ),
      child: const Row(
        children: [
          Icon(
            Icons.search_rounded,
            color: _AppColors.mutedText,
            size: 16,
          ),
          SizedBox(width: 5),
          Text(
            'K/USDT',
            style: TextStyle(
              color: _AppColors.mutedText,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _balanceSection() {
    final balance = _money(user['balance']);

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 18, 14, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Total Assets',
                style: TextStyle(
                  color: _AppColors.secondaryText,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(width: 5),
              GestureDetector(
                onTap: () {
                  setState(() {
                    balanceHidden = !balanceHidden;
                  });
                },
                child: Icon(
                  balanceHidden
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                  color: _AppColors.secondaryText,
                  size: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 5),
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                balanceHidden ? '••••••' : balance,
                style: const TextStyle(
                  color: _AppColors.primaryText,
                  fontSize: 30,
                  height: 1,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.8,
                ),
              ),
              const SizedBox(width: 4),
              const Padding(
                padding: EdgeInsets.only(bottom: 2),
                child: Text(
                  'USD',
                  style: TextStyle(
                    color: _AppColors.secondaryText,
                    fontSize: 10,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              const Spacer(),
              SizedBox(
                width: 64,
                height: 28,
                child: CustomPaint(
                  painter: _MiniChartPainter(),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _actionButtons() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 14, 14, 0),
      child: Row(
        children: [
          Expanded(
            child: SizedBox(
              height: 34,
              child: ElevatedButton(
                onPressed: () => _switchTab(1),
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  backgroundColor: _AppColors.orange,
                  foregroundColor: Colors.black,
                  padding: EdgeInsets.zero,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: const Text(
                  'Buy',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: SizedBox(
              height: 34,
              child: ElevatedButton(
                onPressed: () => _switchTab(2),
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  backgroundColor: _AppColors.surface,
                  foregroundColor: Colors.white,
                  padding: EdgeInsets.zero,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: const Text(
                  'Deposit',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _emptyLightBox() {
    return Container(
      height: 92,
      margin: const EdgeInsets.fromLTRB(14, 12, 14, 0),
      decoration: BoxDecoration(
        color: _AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.white.withOpacity(0.025),
        ),
      ),
    );
  }

  Widget _btcSection() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 17, 14, 0),
      child: Column(
        children: [
          const Row(
            children: [
              Text(
                'Favorites',
                style: TextStyle(
                  color: _AppColors.secondaryText,
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
              SizedBox(width: 10),
              _SmallTab(title: 'Hot', selected: true),
              SizedBox(width: 11),
              _SmallTab(title: 'New'),
              SizedBox(width: 11),
              _SmallTab(title: 'Gainers'),
              SizedBox(width: 11),
              _SmallTab(title: 'Losers'),
            ],
          ),
          const SizedBox(height: 15),
          _coinRow(
            rank: '1',
            symbol: 'BTC',
            name: 'Bitcoin',
            price: '\$113,612.00',
            change: '-0.26%',
            positive: false,
          ),
        ],
      ),
    );
  }

  Widget _coinRow({
    required String rank,
    required String symbol,
    required String name,
    required String price,
    required String change,
    required bool positive,
  }) {
    return SizedBox(
      height: 43,
      child: Row(
        children: [
          SizedBox(
            width: 16,
            child: Text(
              rank,
              style: const TextStyle(
                color: _AppColors.orange,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  symbol,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  name,
                  style: const TextStyle(
                    color: _AppColors.secondaryText,
                    fontSize: 10,
                  ),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                price,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                change,
                style: TextStyle(
                  color: positive ? _AppColors.green : _AppColors.red,
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _homePage() {
    return RefreshIndicator(
      onRefresh: refreshProfile,
      color: _AppColors.orange,
      backgroundColor: _AppColors.surface,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(
          parent: BouncingScrollPhysics(),
        ),
        children: [
          _topBar(),
          _searchBar(),
          _balanceSection(),
          _actionButtons(),
          _emptyLightBox(),
          _btcSection(),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  Widget _body() {
    final role = user['role']?.toString().toLowerCase() ?? '';

    switch (currentIndex) {
      case 0:
        return _homePage();

      case 1:
        return role == 'merchant'
            ? MerchantRequestsScreen(
                onHistoryTap: _openP2PRequestHistory,
              )
            : RequestScreen(
                onHistoryTap: _openP2PRequestHistory,
              );

      case 2:
        return WalletTransferScreen(
          user: user,
          onSuccess: refreshProfile,
          onHistoryTap: _openTransferHistory,
        );

      case 3:
        return HistoryScreen(
          user: user,
          initialType: historyInitialType,
        );

      default:
        return SettingsScreen(
          user: user,
          onProfileUpdated: refreshProfile,
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _AppColors.background,
      body: SafeArea(
        child: loading
            ? const Center(
                child: CircularProgressIndicator(
                  color: _AppColors.orange,
                  strokeWidth: 2,
                ),
              )
            : AnimatedSwitcher(
                duration: const Duration(milliseconds: 180),
                child: KeyedSubtree(
                  key: ValueKey(currentIndex),
                  child: _body(),
                ),
              ),
      ),
      bottomNavigationBar: BottomNav(
        currentIndex: currentIndex,
        onTap: (index) {
          BottomNav.handleTap(
            context,
            index,
            setState: _switchTab,
          );
        },
      ),
    );
  }
}

class _SmallTab extends StatelessWidget {
  final String title;
  final bool selected;

  const _SmallTab({
    required this.title,
    this.selected = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: selected ? 9 : 0,
        vertical: selected ? 5 : 0,
      ),
      decoration: selected
          ? BoxDecoration(
              color: _AppColors.surface,
              borderRadius: BorderRadius.circular(12),
            )
          : null,
      child: Text(
        title,
        style: TextStyle(
          color: selected
              ? _AppColors.primaryText
              : _AppColors.secondaryText,
          fontSize: 10,
          fontWeight: selected
              ? FontWeight.w700
              : FontWeight.w500,
        ),
      ),
    );
  }
}

class _MiniChartPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final path = Path()
      ..moveTo(0, size.height * 0.74)
      ..cubicTo(
        size.width * 0.15,
        size.height * 0.55,
        size.width * 0.25,
        size.height * 0.66,
        size.width * 0.38,
        size.height * 0.54,
      )
      ..cubicTo(
        size.width * 0.50,
        size.height * 0.44,
        size.width * 0.58,
        size.height * 0.62,
        size.width * 0.70,
        size.height * 0.37,
      )
      ..cubicTo(
        size.width * 0.79,
        size.height * 0.19,
        size.width * 0.88,
        size.height * 0.18,
        size.width,
        size.height * 0.48,
      );

    final paint = Paint()
      ..color = _AppColors.orange
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.3
      ..strokeCap = StrokeCap.round;

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
