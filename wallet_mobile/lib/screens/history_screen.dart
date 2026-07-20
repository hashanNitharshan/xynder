import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import 'transaction_detail_screen.dart';

class AppColors {
  static const Color background = Color(0xff000000);
  static const Color divider = Color(0xff1D1D1F);

  static const Color filterBackground = Color(0xff1C1C1E);
  static const Color sheetBackground = Color(0xff121214);

  static const Color green = Color(0xff00C076);
  static const Color red = Color(0xffF6465D);
  static const Color amber = Color(0xffF0B90B);

  static const Color primaryText = Color(0xffF5F5F7);
  static const Color secondaryText = Color(0xff8E8E93);
  static const Color mutedText = Color(0xff56565C);
}

class HistoryScreen extends StatefulWidget {
  final Map user;
  final String initialType;

  const HistoryScreen({
    super.key,
    required this.user,
    this.initialType = 'all',
  });

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  late Map user;

  bool _loading = true;
  bool _refreshing = false;

  List requests = [];
  List transfers = [];

  // Type filter: all | request | transfer
  // Default view is Transfer, as requested.
  late String _selectedType;

  // Sort mode: date_new | date_old
  String _sortMode = 'date_new';

  // Date range filter (From / To). Null means no bound on that side.
  DateTime? _fromDate;
  DateTime? _toDate;

  @override
  void initState() {
    super.initState();

    user = widget.user;

    const allowedTypes = <String>[
      'all',
      'request',
      'transfer',
    ];

    _selectedType = allowedTypes.contains(widget.initialType)
        ? widget.initialType
        : 'all';

    _loadData();
  }

  // ---------------------------------------------------------------------------
  // LOAD TRANSACTIONS
  // ---------------------------------------------------------------------------

  Future<void> _loadData({bool showLoader = true}) async {
    if (showLoader && mounted) {
      setState(() {
        _loading = true;
      });
    }

    try {
      final results = await Future.wait([
        ApiService.myRequests(),
        ApiService.walletTransfers(),
      ]);

      final requestResult = results[0];
      final transferResult = results[1];

      if (requestResult['success'] == true) {
        requests = List.from(requestResult['requests'] ?? []);
      }

      if (transferResult['success'] == true) {
        transfers = List.from(transferResult['transfers'] ?? []);
      }
    } catch (error) {
      debugPrint('History loading error: $error');
    }

    if (mounted) {
      setState(() {
        _loading = false;
        _refreshing = false;
      });
    }
  }

  Future<void> _refreshData() async {
    if (_refreshing) return;

    setState(() {
      _refreshing = true;
    });

    await _loadData(showLoader: false);
  }

  // ---------------------------------------------------------------------------
  // FORMAT HELPERS
  // ---------------------------------------------------------------------------

  double _toDouble(dynamic value) {
    if (value == null) return 0;

    return double.tryParse(
          value.toString().replaceAll(',', '').trim(),
        ) ??
        0;
  }

  String _number(
    dynamic value, {
    int decimals = 2,
  }) {
    final amount = _toDouble(value);

    final parts = amount.toStringAsFixed(decimals).split('.');
    final wholeNumber = parts.first;
    final decimalPart = parts.length > 1 ? parts.last : '';

    final formattedWhole = wholeNumber.replaceAllMapped(
      RegExp(r'\B(?=(\d{3})+(?!\d))'),
      (match) => ',',
    );

    if (decimals == 0) {
      return formattedWhole;
    }

    return '$formattedWhole.$decimalPart';
  }

  String _formatDate(dynamic value) {
    final raw = value?.toString().trim() ?? '';

    if (raw.isEmpty) {
      return '—';
    }

    try {
      DateTime dateTime = DateTime.parse(
        raw.replaceFirst(' ', 'T'),
      );

      if (dateTime.isUtc) {
        dateTime = dateTime.toLocal();
      }

      String twoDigits(int number) {
        return number.toString().padLeft(2, '0');
      }

      return '${dateTime.year}-'
          '${twoDigits(dateTime.month)}-'
          '${twoDigits(dateTime.day)} '
          '${twoDigits(dateTime.hour)}:'
          '${twoDigits(dateTime.minute)}:'
          '${twoDigits(dateTime.second)}';
    } catch (_) {
      return raw;
    }
  }

  // Short date only, used for the date-range pill label (e.g. "12 Jul 2026").
  String _formatShortDate(DateTime date) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  DateTime _sortDate(Map<String, dynamic> item) {
    final raw = item['data']?['created_at']?.toString() ?? '';

    try {
      return DateTime.parse(raw.replaceFirst(' ', 'T'));
    } catch (_) {
      return DateTime.fromMillisecondsSinceEpoch(0);
    }
  }

  // Full, non-merged status so Rejected / Closed / Canceled stay distinct.
  String _normalizeStatus(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    if (sourceType == 'transfer') {
      final transferStatus =
          data['status']?.toString().toLowerCase().trim() ?? '';

      if (transferStatus == 'rejected') {
        return 'rejected';
      }

      if (transferStatus == 'cancelled' ||
          transferStatus == 'canceled' ||
          transferStatus == 'failed') {
        return 'canceled';
      }

      if (transferStatus == 'pending' ||
          transferStatus == 'processing') {
        return 'pending';
      }

      return 'completed';
    }

    final status = data['status']?.toString().toLowerCase().trim() ??
        'pending';

    if (status == 'approved' ||
        status == 'completed' ||
        status == 'success' ||
        status == 'successful') {
      return 'completed';
    }

    if (status == 'rejected') {
      return 'rejected';
    }

    if (status == 'closed') {
      return 'closed';
    }

    if (status == 'cancelled' ||
        status == 'canceled' ||
        status == 'failed') {
      return 'canceled';
    }

    return 'pending';
  }

  String _displayStatus(String normalizedStatus) {
    switch (normalizedStatus) {
      case 'completed':
        return 'Completed';

      case 'pending':
        return 'Pending';

      case 'rejected':
        return 'Rejected';

      case 'closed':
        return 'Closed';

      case 'canceled':
        return 'Canceled';

      default:
        return 'Pending';
    }
  }

  Color _statusColor(String normalizedStatus) {
    switch (normalizedStatus) {
      case 'completed':
        return AppColors.green;

      case 'rejected':
      case 'canceled':
        return AppColors.red;

      case 'closed':
        return AppColors.mutedText;

      default:
        return AppColors.amber;
    }
  }

  // ---------------------------------------------------------------------------
  // TRANSACTION DATA HELPERS
  // ---------------------------------------------------------------------------

  String _transactionType(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    if (sourceType == 'transfer') {
      final currentUserId =
          user['id']?.toString() ?? widget.user['id']?.toString() ?? '';

      final senderId = data['sender_id']?.toString() ?? '';

      if (currentUserId.isNotEmpty && senderId == currentUserId) {
        return 'Send';
      }

      return 'Receive';
    }

    final requestType =
        data['type']?.toString().toLowerCase().trim() ?? '';

    if (requestType == 'withdrawal' ||
        requestType == 'sell' ||
        requestType == 'selling') {
      return 'Sell';
    }

    return 'Buy';
  }

  String _transactionTitle(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    final type = _transactionType(sourceType, data);

    if (sourceType == 'transfer') {
      return '$type USD';
    }

    return '$type USDT';
  }

  Color _transactionTitleColor(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    final type = _transactionType(sourceType, data);

    if (type == 'Sell' || type == 'Send') {
      return AppColors.red;
    }

    return AppColors.green;
  }

  String _orderNumber(Map<String, dynamic> data) {
    final transactionNumber =
        data['transaction_no']?.toString().trim() ?? '';

    if (transactionNumber.isNotEmpty &&
        transactionNumber.toLowerCase() != 'null') {
      return transactionNumber;
    }

    return data['id']?.toString() ?? '—';
  }

  dynamic _quantity(Map<String, dynamic> data) {
    return data['amount'] ?? data['qty'] ?? data['quantity'] ?? 0;
  }

  dynamic _localAmount(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    if (sourceType == 'transfer') {
      return data['amount'] ?? 0;
    }

    return data['total_amount'] ??
        data['converted_amount'] ??
        data['local_amount'] ??
        data['inr_amount'] ??
        data['lkr_amount'] ??
        data['amount'] ??
        0;
  }

  double _price(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    final directPrice = _toDouble(
      data['price'] ??
          data['rate'] ??
          data['usd_rate'] ??
          data['inr_rate'] ??
          data['exchange_rate'],
    );

    if (directPrice > 0) {
      return directPrice;
    }

    if (sourceType == 'transfer') {
      return 0;
    }

    final quantity = _toDouble(_quantity(data));

    final convertedAmount = _toDouble(
      data['converted_amount'] ??
          data['local_amount'] ??
          data['lkr_amount'] ??
          data['inr_amount'],
    );

    if (quantity > 0 && convertedAmount > 0) {
      return convertedAmount / quantity;
    }

    return 0;
  }

  String _currencyCode(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    if (sourceType == 'transfer') {
      return 'USD';
    }

    final currency =
        data['currency']?.toString().toUpperCase().trim() ?? '';

    if (currency.isNotEmpty && currency != 'NULL') {
      return currency;
    }

    return 'LKR';
  }

  String _quantityCurrency(String sourceType) {
    return sourceType == 'transfer' ? 'USD' : 'USDT';
  }

  String _counterpartyName(
    String sourceType,
    Map<String, dynamic> data,
  ) {
    final possibleNames = [
      data['merchant_name'],
      data['receiver_name'],
      data['sender_name'],
      data['user_name'],
      data['name'],
      data['merchant'] is Map ? data['merchant']['name'] : null,
      data['receiver'] is Map ? data['receiver']['name'] : null,
      data['sender'] is Map ? data['sender']['name'] : null,
      data['user'] is Map ? data['user']['name'] : null,
    ];

    for (final value in possibleNames) {
      final text = value?.toString().trim() ?? '';

      if (text.isNotEmpty && text.toLowerCase() != 'null') {
        return text;
      }
    }

    if (sourceType == 'transfer') {
      return 'Wallet Transfer';
    }

    return 'Merchant';
  }

  // ---------------------------------------------------------------------------
  // DATE RANGE FILTER
  // ---------------------------------------------------------------------------

  bool get _hasDateRange => _fromDate != null || _toDate != null;

  String _dateRangeLabel() {
    if (_fromDate == null && _toDate == null) {
      return 'All time';
    }

    if (_fromDate != null && _toDate != null) {
      return '${_formatShortDate(_fromDate!)} - ${_formatShortDate(_toDate!)}';
    }

    if (_fromDate != null) {
      return 'From ${_formatShortDate(_fromDate!)}';
    }

    return 'Until ${_formatShortDate(_toDate!)}';
  }

  bool _withinDateRange(DateTime date) {
    if (_fromDate != null) {
      final start = DateTime(
        _fromDate!.year,
        _fromDate!.month,
        _fromDate!.day,
      );

      if (date.isBefore(start)) {
        return false;
      }
    }

    if (_toDate != null) {
      // Make the "to" date inclusive of the whole day.
      final end = DateTime(
        _toDate!.year,
        _toDate!.month,
        _toDate!.day,
        23,
        59,
        59,
        999,
      );

      if (date.isAfter(end)) {
        return false;
      }
    }

    return true;
  }
Future<void> _pickDateRange() async {
    final now = DateTime.now();

    DateTime? tempFrom = _fromDate;
    DateTime? tempTo = _toDate;

    await showDialog<void>(
      context: context,
      barrierColor: Colors.black.withOpacity(0.78),
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            Future<void> pickSingleDate({
              required bool isFrom,
            }) async {
              final initialDate = isFrom
                  ? (tempFrom ?? tempTo ?? now)
                  : (tempTo ?? tempFrom ?? now);

              final picked = await showDatePicker(
                context: context,
                initialDate: initialDate,
                firstDate: DateTime(now.year - 5),
                lastDate: DateTime(now.year + 1),
                helpText:
                    isFrom ? 'SELECT FROM DATE' : 'SELECT TO DATE',
                cancelText: 'CANCEL',
                confirmText: 'SELECT',
                builder: (context, child) {
                  final theme = ThemeData.dark().copyWith(
                    scaffoldBackgroundColor:
                        AppColors.sheetBackground,
                    dialogBackgroundColor:
                        AppColors.sheetBackground,
                    visualDensity: const VisualDensity(
                      horizontal: -3,
                      vertical: -3,
                    ),
                    materialTapTargetSize:
                        MaterialTapTargetSize.shrinkWrap,
                    colorScheme: const ColorScheme.dark(
                      primary: AppColors.amber,
                      onPrimary: Colors.black,
                      surface: AppColors.sheetBackground,
                      onSurface: AppColors.primaryText,
                    ),
                    textButtonTheme: TextButtonThemeData(
                      style: TextButton.styleFrom(
                        foregroundColor: AppColors.amber,
                        minimumSize: const Size(48, 28),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        textStyle: const TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    datePickerTheme: DatePickerThemeData(
                      backgroundColor:
                          AppColors.sheetBackground,
                      surfaceTintColor: Colors.transparent,
                      headerBackgroundColor:
                          AppColors.filterBackground,
                      headerForegroundColor:
                          AppColors.primaryText,
                      dividerColor: AppColors.divider,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(15),
                        side: const BorderSide(
                          color: AppColors.divider,
                          width: 0.8,
                        ),
                      ),
                      headerHeadlineStyle: const TextStyle(
                        color: AppColors.primaryText,
                        fontSize: 17,
                        fontWeight: FontWeight.w700,
                      ),
                      headerHelpStyle: const TextStyle(
                        color: AppColors.secondaryText,
                        fontSize: 8,
                        fontWeight: FontWeight.w600,
                      ),
                      weekdayStyle: const TextStyle(
                        color: AppColors.secondaryText,
                        fontSize: 8,
                        fontWeight: FontWeight.w600,
                      ),
                      dayStyle: const TextStyle(
                        color: AppColors.primaryText,
                        fontSize: 9,
                        fontWeight: FontWeight.w500,
                      ),
                      yearStyle: const TextStyle(
                        color: AppColors.primaryText,
                        fontSize: 9,
                        fontWeight: FontWeight.w500,
                      ),
                      todayForegroundColor:
                          WidgetStateProperty.resolveWith<Color?>(
                        (states) {
                          if (states.contains(
                            WidgetState.selected,
                          )) {
                            return Colors.black;
                          }

                          return AppColors.amber;
                        },
                      ),
                      todayBorder: const BorderSide(
                        color: AppColors.amber,
                        width: 0.8,
                      ),
                      dayForegroundColor:
                          WidgetStateProperty.resolveWith<Color?>(
                        (states) {
                          if (states.contains(
                            WidgetState.selected,
                          )) {
                            return Colors.black;
                          }

                          if (states.contains(
                            WidgetState.disabled,
                          )) {
                            return AppColors.mutedText;
                          }

                          return AppColors.primaryText;
                        },
                      ),
                      dayBackgroundColor:
                          WidgetStateProperty.resolveWith<Color?>(
                        (states) {
                          if (states.contains(
                            WidgetState.selected,
                          )) {
                            return AppColors.amber;
                          }

                          return Colors.transparent;
                        },
                      ),
                      yearForegroundColor:
                          WidgetStateProperty.resolveWith<Color?>(
                        (states) {
                          if (states.contains(
                            WidgetState.selected,
                          )) {
                            return Colors.black;
                          }

                          return AppColors.primaryText;
                        },
                      ),
                      yearBackgroundColor:
                          WidgetStateProperty.resolveWith<Color?>(
                        (states) {
                          if (states.contains(
                            WidgetState.selected,
                          )) {
                            return AppColors.amber;
                          }

                          return Colors.transparent;
                        },
                      ),
                    ),
                  );

                  return Theme(
                    data: theme,
                    child: MediaQuery(
                      data: MediaQuery.of(context).copyWith(
                        textScaler:
                            const TextScaler.linear(0.78),
                      ),
                      child: Center(
                        child: Transform.scale(
                          scale: 0.76,
                          alignment: Alignment.center,
                          child: child!,
                        ),
                      ),
                    ),
                  );
                },
              );

              if (picked == null) {
                return;
              }

              setDialogState(() {
                if (isFrom) {
                  tempFrom = picked;

                  if (tempTo != null &&
                      tempTo!.isBefore(picked)) {
                    tempTo = picked;
                  }
                } else {
                  tempTo = picked;

                  if (tempFrom != null &&
                      tempFrom!.isAfter(picked)) {
                    tempFrom = picked;
                  }
                }
              });
            }

            Widget dateBox({
              required String label,
              required DateTime? value,
              required VoidCallback onTap,
            }) {
              final selected = value != null;

              return Expanded(
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: onTap,
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      height: 52,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                      ),
                      decoration: BoxDecoration(
                        color: AppColors.filterBackground,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: selected
                              ? AppColors.amber
                              : AppColors.divider,
                          width: selected ? 1.1 : 0.8,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: selected
                                  ? AppColors.amber.withOpacity(
                                      0.12,
                                    )
                                  : Colors.transparent,
                              borderRadius:
                                  BorderRadius.circular(8),
                            ),
                            child: Icon(
                              Icons.calendar_month_rounded,
                              color: selected
                                  ? AppColors.amber
                                  : AppColors.secondaryText,
                              size: 16,
                            ),
                          ),
                          const SizedBox(width: 9),
                          Expanded(
                            child: Column(
                              mainAxisAlignment:
                                  MainAxisAlignment.center,
                              crossAxisAlignment:
                                  CrossAxisAlignment.start,
                              children: [
                                Text(
                                  label,
                                  style: const TextStyle(
                                    color: AppColors.mutedText,
                                    fontSize: 9,
                                    fontWeight:
                                        FontWeight.w600,
                                    letterSpacing: 0.4,
                                  ),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  value == null
                                      ? 'Select date'
                                      : _formatShortDate(value),
                                  maxLines: 1,
                                  overflow:
                                      TextOverflow.ellipsis,
                                  style: TextStyle(
                                    color: selected
                                        ? AppColors.primaryText
                                        : AppColors
                                            .secondaryText,
                                    fontSize: 11,
                                    fontWeight: selected
                                        ? FontWeight.w700
                                        : FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            }

            return Dialog(
              backgroundColor: Colors.transparent,
              insetPadding: const EdgeInsets.symmetric(
                horizontal: 24,
                vertical: 24,
              ),
              child: Container(
                width: double.infinity,
                constraints: const BoxConstraints(
                  maxWidth: 360,
                ),
                padding: const EdgeInsets.fromLTRB(
                  18,
                  18,
                  18,
                  14,
                ),
                decoration: BoxDecoration(
                  color: AppColors.sheetBackground,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: AppColors.divider,
                    width: 0.8,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.45),
                      blurRadius: 30,
                      offset: const Offset(0, 12),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Expanded(
                          child: Text(
                            'Select Date Range',
                            style: TextStyle(
                              color: AppColors.primaryText,
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                        GestureDetector(
                          onTap: () =>
                              Navigator.pop(dialogContext),
                          behavior: HitTestBehavior.opaque,
                          child: const Padding(
                            padding: EdgeInsets.all(3),
                            child: Icon(
                              Icons.close_rounded,
                              color:
                                  AppColors.secondaryText,
                              size: 20,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 5),
                    const Text(
                      'Choose the starting and ending dates',
                      style: TextStyle(
                        color: AppColors.mutedText,
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        dateBox(
                          label: 'FROM',
                          value: tempFrom,
                          onTap: () {
                            pickSingleDate(isFrom: true);
                          },
                        ),
                        const SizedBox(width: 10),
                        dateBox(
                          label: 'TO',
                          value: tempTo,
                          onTap: () {
                            pickSingleDate(isFrom: false);
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        TextButton(
                          onPressed: () {
                            setDialogState(() {
                              tempFrom = null;
                              tempTo = null;
                            });
                          },
                          style: TextButton.styleFrom(
                            minimumSize:
                                const Size(60, 38),
                            padding:
                                const EdgeInsets.symmetric(
                              horizontal: 10,
                            ),
                          ),
                          child: const Text(
                            'Clear',
                            style: TextStyle(
                              color:
                                  AppColors.secondaryText,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        const Spacer(),
                        SizedBox(
                          height: 38,
                          child: OutlinedButton(
                            onPressed: () {
                              Navigator.pop(dialogContext);
                            },
                            style:
                                OutlinedButton.styleFrom(
                              foregroundColor:
                                  AppColors.secondaryText,
                              side: const BorderSide(
                                color: AppColors.divider,
                                width: 0.8,
                              ),
                              padding:
                                  const EdgeInsets.symmetric(
                                horizontal: 16,
                              ),
                              shape:
                                  RoundedRectangleBorder(
                                borderRadius:
                                    BorderRadius.circular(19),
                              ),
                            ),
                            child: const Text(
                              'Cancel',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight:
                                    FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        SizedBox(
                          height: 38,
                          child: ElevatedButton(
                            onPressed: () {
                              setState(() {
                                _fromDate = tempFrom;
                                _toDate = tempTo;
                              });

                              Navigator.pop(dialogContext);
                            },
                            style:
                                ElevatedButton.styleFrom(
                              backgroundColor:
                                  AppColors.amber,
                              foregroundColor: Colors.black,
                              elevation: 0,
                              padding:
                                  const EdgeInsets.symmetric(
                                horizontal: 20,
                              ),
                              shape:
                                  RoundedRectangleBorder(
                                borderRadius:
                                    BorderRadius.circular(19),
                              ),
                            ),
                            child: const Text(
                              'Apply',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight:
                                    FontWeight.w800,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }


  void _clearDateRange() {
    setState(() {
      _fromDate = null;
      _toDate = null;
    });
  }

  // ---------------------------------------------------------------------------
  // FILTERED + SORTED DATA
  // ---------------------------------------------------------------------------

  List<Map<String, dynamic>> _historyItems() {
    final items = <Map<String, dynamic>>[];

    for (final request in requests) {
      final data = Map<String, dynamic>.from(request);

      items.add({
        'source_type': 'request',
        'data': data,
      });
    }

    for (final transfer in transfers) {
      final data = Map<String, dynamic>.from(transfer);

      items.add({
        'source_type': 'transfer',
        'data': data,
      });
    }

    // ---- type filter ----
    var filtered = items.where((item) {
      if (_selectedType == 'all') return true;
      return item['source_type'].toString() == _selectedType;
    }).toList();


    // ---- date range filter ----
    if (_hasDateRange) {
      filtered = filtered.where((item) {
        return _withinDateRange(_sortDate(item));
      }).toList();
    }

    // ---- sort ----
    filtered.sort((a, b) {
      if (_sortMode == 'date_old') {
        return _sortDate(a).compareTo(_sortDate(b));
      }

      return _sortDate(b).compareTo(_sortDate(a));
    });

    return filtered;
  }

  // ---------------------------------------------------------------------------
  // TOP BAR
  // ---------------------------------------------------------------------------

  Widget _topBar() {
    final canPop = Navigator.canPop(context);

    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 10, 16, 4),
      child: Row(
        children: [
          SizedBox(
            width: 44,
            child: canPop
                ? IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(
                      Icons.arrow_back_rounded,
                      color: AppColors.primaryText,
                      size: 22,
                    ),
                  )
                : null,
          ),
          const Expanded(
            child: Text(
              'Orders',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppColors.primaryText,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          IconButton(
            onPressed: _openFilterSheet,
            icon: const Icon(
              Icons.filter_list_rounded,
              color: AppColors.primaryText,
              size: 23,
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // TYPE FILTER (All / P2P (Request) / Transfer)
  // ---------------------------------------------------------------------------

  Widget _typeFilterSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(16, 6, 16, 10),
      child: Row(
        children: [
          Expanded(
            child: _pillButton(
              title: 'All',
              selected: _selectedType == 'all',
              onTap: () => setState(() => _selectedType = 'all'),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _pillButton(
              title: 'P2P Request',
              selected: _selectedType == 'request',
              onTap: () => setState(() => _selectedType = 'request'),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _pillButton(
              title: 'Transfer',
              selected: _selectedType == 'transfer',
              onTap: () => setState(() => _selectedType = 'transfer'),
            ),
          ),
        ],
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // DATE RANGE FILTER SECTION (From / To calendar picker)
  // ---------------------------------------------------------------------------

  Widget _dateRangeSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: _pickDateRange,
              child: Container(
                height: 42,
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: _hasDateRange
                      ? AppColors.filterBackground
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(
                    color: _hasDateRange
                        ? AppColors.amber
                        : AppColors.divider,
                    width: _hasDateRange ? 1.2 : 0.8,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.calendar_month_rounded,
                      color: _hasDateRange
                          ? AppColors.amber
                          : AppColors.secondaryText,
                      size: 17,
                    ),
                    const SizedBox(width: 9),
                    Expanded(
                      child: Text(
                        _dateRangeLabel(),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: _hasDateRange
                              ? AppColors.primaryText
                              : AppColors.mutedText,
                          fontSize: 13,
                          fontWeight: _hasDateRange
                              ? FontWeight.w700
                              : FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          if (_hasDateRange) ...[
            const SizedBox(width: 8),
            GestureDetector(
              onTap: _clearDateRange,
              child: Container(
                width: 42,
                height: 42,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: AppColors.filterBackground,
                  borderRadius: BorderRadius.circular(21),
                  border: Border.all(color: AppColors.divider, width: 0.8),
                ),
                child: const Icon(
                  Icons.close_rounded,
                  color: AppColors.secondaryText,
                  size: 18,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _pillButton({
    required String title,
    required bool selected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        height: 34,
        padding: const EdgeInsets.symmetric(horizontal: 14),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: selected
              ? AppColors.filterBackground
              : Colors.transparent,
          borderRadius: BorderRadius.circular(22),
          border: selected
              ? null
              : Border.all(
                  color: AppColors.divider,
                  width: 0.8,
                ),
        ),
        child: Text(
          title,
          style: TextStyle(
            color: selected
                ? AppColors.primaryText
                : AppColors.mutedText,
            fontSize: 12,
            fontWeight: selected
                ? FontWeight.w700
                : FontWeight.w500,
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // FILTER BOTTOM SHEET (date-wise / letter-wise sort + date range)
  // ---------------------------------------------------------------------------
void _openFilterSheet() {
  showDialog<void>(
    context: context,
    barrierColor: Colors.black.withOpacity(0.75),
    builder: (dialogContext) {
      String tempSort = _sortMode;

      return StatefulBuilder(
        builder: (context, setDialogState) {
          Widget option({
            required String title,
            required String subtitle,
            required String value,
            required IconData icon,
          }) {
            final selected = tempSort == value;

            return Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  setDialogState(() {
                    tempSort = value;
                  });
                },
                borderRadius: BorderRadius.circular(13),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 160),
                  height: 58,
                  margin: const EdgeInsets.only(bottom: 10),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 13,
                  ),
                  decoration: BoxDecoration(
                    color: selected
                        ? AppColors.filterBackground
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(13),
                    border: Border.all(
                      color: selected
                          ? AppColors.amber
                          : AppColors.divider,
                      width: selected ? 1.1 : 0.8,
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 34,
                        height: 34,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: selected
                              ? AppColors.amber.withOpacity(0.13)
                              : AppColors.filterBackground,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          icon,
                          color: selected
                              ? AppColors.amber
                              : AppColors.secondaryText,
                          size: 18,
                        ),
                      ),
                      const SizedBox(width: 11),
                      Expanded(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              title,
                              style: TextStyle(
                                color: selected
                                    ? AppColors.primaryText
                                    : AppColors.secondaryText,
                                fontSize: 13,
                                fontWeight: selected
                                    ? FontWeight.w700
                                    : FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              subtitle,
                              style: const TextStyle(
                                color: AppColors.mutedText,
                                fontSize: 10,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Icon(
                        selected
                            ? Icons.radio_button_checked_rounded
                            : Icons.radio_button_off_rounded,
                        color: selected
                            ? AppColors.amber
                            : AppColors.mutedText,
                        size: 18,
                      ),
                    ],
                  ),
                ),
              ),
            );
          }

          return Dialog(
            backgroundColor: Colors.transparent,
            insetPadding: const EdgeInsets.symmetric(
              horizontal: 24,
              vertical: 24,
            ),
            child: Container(
              width: double.infinity,
              constraints: const BoxConstraints(
                maxWidth: 360,
              ),
              padding: const EdgeInsets.fromLTRB(
                18,
                18,
                18,
                15,
              ),
              decoration: BoxDecoration(
                color: AppColors.sheetBackground,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: AppColors.divider,
                  width: 0.8,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.45),
                    blurRadius: 30,
                    offset: const Offset(0, 12),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Expanded(
                        child: Text(
                          'Filter Orders',
                          style: TextStyle(
                            color: AppColors.primaryText,
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      GestureDetector(
                        onTap: () {
                          Navigator.pop(dialogContext);
                        },
                        behavior: HitTestBehavior.opaque,
                        child: const Padding(
                          padding: EdgeInsets.all(3),
                          child: Icon(
                            Icons.close_rounded,
                            color: AppColors.secondaryText,
                            size: 20,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),
                  const Text(
                    'Choose how transactions are displayed',
                    style: TextStyle(
                      color: AppColors.mutedText,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 17),
                  const Text(
                    'DATE SORT',
                    style: TextStyle(
                      color: AppColors.secondaryText,
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.7,
                    ),
                  ),
                  const SizedBox(height: 9),
                  option(
                    title: 'Newest first',
                    subtitle: 'Show the latest transactions first',
                    value: 'date_new',
                    icon: Icons.south_rounded,
                  ),
                  option(
                    title: 'Oldest first',
                    subtitle: 'Show the oldest transactions first',
                    value: 'date_old',
                    icon: Icons.north_rounded,
                  ),
                  const SizedBox(height: 3),
                  Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () {
                        Navigator.pop(dialogContext);

                        Future.delayed(
                          const Duration(milliseconds: 120),
                          () {
                            if (mounted) {
                              _pickDateRange();
                            }
                          },
                        );
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        height: 45,
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                        ),
                        decoration: BoxDecoration(
                          color: _hasDateRange
                              ? AppColors.filterBackground
                              : Colors.transparent,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: _hasDateRange
                                ? AppColors.amber
                                : AppColors.divider,
                            width: _hasDateRange ? 1.1 : 0.8,
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.calendar_month_rounded,
                              color: _hasDateRange
                                  ? AppColors.amber
                                  : AppColors.secondaryText,
                              size: 17,
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                _hasDateRange
                                    ? _dateRangeLabel()
                                    : 'Select date range',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  color: _hasDateRange
                                      ? AppColors.primaryText
                                      : AppColors.secondaryText,
                                  fontSize: 12,
                                  fontWeight: _hasDateRange
                                      ? FontWeight.w700
                                      : FontWeight.w600,
                                ),
                              ),
                            ),
                            const Icon(
                              Icons.chevron_right_rounded,
                              color: AppColors.mutedText,
                              size: 19,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Row(
                    children: [
                      SizedBox(
                        height: 38,
                        child: TextButton(
                          onPressed: () {
                            setDialogState(() {
                              tempSort = 'date_new';
                            });

                            setState(() {
                              _sortMode = 'date_new';
                              _fromDate = null;
                              _toDate = null;
                            });

                            Navigator.pop(dialogContext);
                          },
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                            ),
                          ),
                          child: const Text(
                            'Reset',
                            style: TextStyle(
                              color: AppColors.secondaryText,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                      const Spacer(),
                      SizedBox(
                        height: 38,
                        child: ElevatedButton(
                          onPressed: () {
                            setState(() {
                              _sortMode = tempSort;
                            });

                            Navigator.pop(dialogContext);
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.amber,
                            foregroundColor: Colors.black,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 24,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(19),
                            ),
                          ),
                          child: const Text(
                            'Apply Filter',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      );
    },
  );
}

  // ---------------------------------------------------------------------------
  // HISTORY LIST
  // ---------------------------------------------------------------------------

  Widget _historyList() {
    final items = _historyItems();

    if (items.isEmpty) {
      return SliverFillRemaining(
        hasScrollBody: false,
        child: _emptyState(),
      );
    }

    return SliverList(
      delegate: SliverChildBuilderDelegate(
        (context, index) {
          return _transactionItem(items[index]);
        },
        childCount: items.length,
      ),
    );
  }

  Widget _transactionItem(Map<String, dynamic> item) {
    final sourceType = item['source_type'].toString();

    final data = Map<String, dynamic>.from(
      item['data'] as Map,
    );

    final normalizedStatus = _normalizeStatus(
      sourceType,
      data,
    );

    final statusText = _displayStatus(normalizedStatus);
    final statusColor = _statusColor(normalizedStatus);

    final title = _transactionTitle(sourceType, data);

    final titleColor = _transactionTitleColor(
      sourceType,
      data,
    );

    final orderNumber = _orderNumber(data);
    final counterparty = _counterpartyName(sourceType, data);

    final localAmount = _localAmount(sourceType, data);
    final quantity = _quantity(data);
    final price = _price(sourceType, data);

    final currencyCode = _currencyCode(sourceType, data);
    final quantityCurrency = _quantityCurrency(sourceType);

    return InkWell(
      onTap: () async {
        await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => TransactionDetailScreen(
              item: data,
              sourceType: sourceType,
              user: user,
            ),
          ),
        );

        if (mounted) {
          await _loadData(showLoader: false);
        }
      },
      splashColor: Colors.white.withOpacity(0.03),
      highlightColor: Colors.white.withOpacity(0.02),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20),
        padding: const EdgeInsets.fromLTRB(0, 7, 0, 7),
        decoration: const BoxDecoration(
          border: Border(
            bottom: BorderSide(
              color: AppColors.divider,
              width: 0.8,
            ),
          ),
        ),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: RichText(
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    text: TextSpan(
                      children: [
                        TextSpan(
                          text: title.split(' ').first,
                          style: TextStyle(
                            color: titleColor,
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            height: 1.1,
                          ),
                        ),
                        TextSpan(
                          text: title.contains(' ')
                              ? ' ${title.substring(title.indexOf(' ') + 1)}'
                              : '',
                          style: const TextStyle(
                            color: AppColors.primaryText,
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            height: 1.1,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  statusText,
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(width: 5),
                const Icon(
                  Icons.chevron_right_rounded,
                  color: AppColors.mutedText,
                  size: 18,
                ),
              ],
            ),

            const SizedBox(height: 5),

            _informationRow(
              label: 'Amount',
              value: '${_number(localAmount)} $currencyCode',
              valueFontSize: 15,
              valueFontWeight: FontWeight.w600,
              valueColor: AppColors.primaryText,
            ),

            const SizedBox(height: 4),

            _informationRow(
              label: 'Price',
              value: price > 0
                  ? '${_number(price)} $currencyCode'
                  : '—',
            ),

            const SizedBox(height: 4),

            _informationRow(
              label: 'Qty',
              value: '${_number(quantity, decimals: 4)} $quantityCurrency',
            ),

            const SizedBox(height: 4),

            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const Text(
                  'Order No.',
                  style: TextStyle(
                    color: AppColors.secondaryText,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(width: 15),
                Expanded(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Flexible(
                        child: Text(
                          orderNumber,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          textAlign: TextAlign.right,
                          style: const TextStyle(
                            color: AppColors.primaryText,
                            fontSize: 10.5,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      const SizedBox(width: 7),
                      GestureDetector(
                        onTap: () {
                          _copyOrderNumber(orderNumber);
                        },
                        behavior: HitTestBehavior.opaque,
                        child: const Padding(
                          padding: EdgeInsets.all(2),
                          child: Icon(
                            Icons.copy_rounded,
                            color: AppColors.secondaryText,
                            size: 17,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 5),

            Row(
              children: [
                Flexible(
                  child: Container(
                    constraints: const BoxConstraints(
                      minHeight: 27,
                      maxWidth: 190,
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(
                        color: AppColors.secondaryText.withOpacity(0.45),
                      ),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.chat_bubble_outline_rounded,
                          color: AppColors.secondaryText,
                          size: 14,
                        ),
                        const SizedBox(width: 6),
                        Flexible(
                          child: Text(
                            counterparty,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: AppColors.primaryText,
                              fontSize: 11,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    _formatDate(data['created_at']),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    textAlign: TextAlign.right,
                    style: const TextStyle(
                      color: AppColors.mutedText,
                      fontSize: 10.5,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _informationRow({
    required String label,
    required String value,
    Color valueColor = AppColors.primaryText,
    double valueFontSize = 12,
    FontWeight valueFontWeight = FontWeight.w500,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: AppColors.secondaryText,
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.right,
            style: TextStyle(
              color: valueColor,
              fontSize: valueFontSize,
              fontWeight: valueFontWeight,
            ),
          ),
        ),
      ],
    );
  }

  Future<void> _copyOrderNumber(String orderNumber) async {
    if (orderNumber.isEmpty || orderNumber == '—') {
      return;
    }

    await Clipboard.setData(
      ClipboardData(text: orderNumber),
    );

    if (!mounted) return;

    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          duration: const Duration(seconds: 1),
          behavior: SnackBarBehavior.floating,
          margin: const EdgeInsets.fromLTRB(20, 0, 20, 18),
          backgroundColor: const Color(0xff1C1C1E),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(9),
          ),
          content: const Text(
            'Order number copied',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: AppColors.primaryText,
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      );
  }

  Widget _emptyState() {
    final message = _hasDateRange
        ? 'No transactions in the selected date range'
        : 'No transactions available';

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: 30,
          vertical: 70,
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 62,
              height: 62,
              decoration: const BoxDecoration(
                color: AppColors.filterBackground,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.receipt_long_outlined,
                color: AppColors.secondaryText,
                size: 27,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: AppColors.secondaryText,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
            if (_hasDateRange) ...[
              const SizedBox(height: 10),
              GestureDetector(
                onTap: _clearDateRange,
                child: const Text(
                  'Clear date range',
                  style: TextStyle(
                    color: AppColors.amber,
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // SCREEN
  // ---------------------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        top: true,
        child: _loading
            ? const Center(
                child: CircularProgressIndicator(
                  color: AppColors.amber,
                  strokeWidth: 2.5,
                ),
              )
            : RefreshIndicator(
                onRefresh: _refreshData,
                color: AppColors.amber,
                backgroundColor: AppColors.filterBackground,
                child: CustomScrollView(
                  physics: const AlwaysScrollableScrollPhysics(
                    parent: BouncingScrollPhysics(),
                  ),
                  slivers: [
                    SliverToBoxAdapter(
                      child: _topBar(),
                    ),
                    SliverToBoxAdapter(
                      child: _typeFilterSection(),
                    ),
                    SliverToBoxAdapter(
                      child: _dateRangeSection(),
                    ),
                    _historyList(),
                    const SliverToBoxAdapter(
                      child: SizedBox(height: 90),
                    ),
                  ],
                ),
              ),
      ),
    );
  }
}