import 'dart:async';
import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../services/binance_stream.dart';
import '../services/market_controller.dart';
import 'candle_chart.dart';

const Color _sheetBg = Color(0xFF1E2329);
const Color _inputBg = Color(0xFF0B0E11);
const Color _border = Color(0xFF2B3139);
const Color _text = Color(0xFFEAECEF);
const Color _muted = Color(0xFF848E9C);
const Color _faint = Color(0xFF5E6673);

/* ================================================================
   Coin icon
   ================================================================ */

/// Round coin logo. Shows a letter badge when the logo cannot load.
class CoinIcon extends StatelessWidget {
  final String symbol;
  final double size;

  const CoinIcon({super.key, required this.symbol, this.size = 28});

  static const List<Color> _palette = [
    Color(0xFFF0B90B),
    Color(0xFF627EEA),
    Color(0xFF0ECB81),
    Color(0xFFE056FD),
    Color(0xFF26A17B),
    Color(0xFFF6465D),
    Color(0xFF8C7CF0),
    Color(0xFF2A5ADA),
  ];

  Widget _fallback() {
    final base = baseOf(symbol);
    final color = _palette[base.codeUnits.fold<int>(0, (a, b) => a + b) %
        _palette.length];
    return Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.18),
        shape: BoxShape.circle,
      ),
      child: Text(
        base.isEmpty ? '?' : base.substring(0, 1),
        style: TextStyle(
          color: color,
          fontSize: size * 0.45,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ClipOval(
      child: Image.network(
        coinIconUrl(symbol),
        width: size,
        height: size,
        fit: BoxFit.cover,
        errorBuilder: (_, __, ___) => _fallback(),
        loadingBuilder: (context, child, progress) =>
            progress == null ? child : _fallback(),
      ),
    );
  }
}

/* ================================================================
   Coin picker (bottom sheet)
   ================================================================ */

/// Opens the coin picker. Returns the chosen symbol, or null.
Future<String?> showPairPicker(
  BuildContext context, {
  required String current,
  List<String> symbols = kMarketSymbols,
  Color accent = const Color(0xFFF0B90B),
}) {
  Favorites.load();
  return showModalBottomSheet<String>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _sheetBg,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
    ),
    builder: (_) => _PairSheet(
      current: current,
      symbols: symbols,
      accent: accent,
    ),
  );
}

class _PairSheet extends StatefulWidget {
  final String current;
  final List<String> symbols;
  final Color accent;

  const _PairSheet({
    required this.current,
    required this.symbols,
    required this.accent,
  });

  @override
  State<_PairSheet> createState() => _PairSheetState();
}

class _PairSheetState extends State<_PairSheet> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final q = _query.trim().toUpperCase();
    final list = q.isEmpty
        ? widget.symbols
        : widget.symbols
            .where((s) =>
                baseOf(s).contains(q) || coinName(s).toUpperCase().contains(q))
            .toList();
    final media = MediaQuery.of(context);

    return Padding(
      padding: EdgeInsets.only(bottom: media.viewInsets.bottom),
      child: SizedBox(
        height: media.size.height * 0.7,
        child: Column(
          children: [
            const SizedBox(height: 10),
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(
                color: _border,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const Padding(
              padding: EdgeInsets.fromLTRB(16, 14, 16, 0),
              child: Row(
                children: [
                  Text(
                    'Select Market',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: TextField(
                autofocus: false,
                style: const TextStyle(color: _text, fontSize: 13),
                cursorColor: widget.accent,
                onChanged: (v) => setState(() => _query = v),
                onSubmitted: (_) {
                  if (list.isNotEmpty) Navigator.pop(context, list.first);
                },
                decoration: InputDecoration(
                  isDense: true,
                  hintText: 'Search coin',
                  hintStyle: const TextStyle(color: _faint, fontSize: 13),
                  prefixIcon: const Icon(
                    Icons.search_rounded,
                    color: _faint,
                    size: 18,
                  ),
                  filled: true,
                  fillColor: _inputBg,
                  contentPadding: const EdgeInsets.symmetric(vertical: 10),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: const BorderSide(color: _border),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide(color: widget.accent),
                  ),
                ),
              ),
            ),
            Expanded(
              child: list.isEmpty
                  ? const Center(
                      child: Text(
                        'No coin found',
                        style: TextStyle(color: _faint, fontSize: 12),
                      ),
                    )
                  : ValueListenableBuilder<Set<String>>(
                      valueListenable: Favorites.notifier,
                      builder: (context, favs, _) => ListView.builder(
                        itemCount: list.length,
                        itemBuilder: (_, i) {
                          final s = list[i];
                          final active = s == widget.current;
                          final fav = favs.contains(s);
                          return InkWell(
                            onTap: () => Navigator.pop(context, s),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 10,
                              ),
                              child: Row(
                                children: [
                                  CoinIcon(symbol: s, size: 26),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text.rich(
                                          TextSpan(
                                            children: [
                                              TextSpan(
                                                text: baseOf(s),
                                                style: TextStyle(
                                                  color: active
                                                      ? widget.accent
                                                      : Colors.white,
                                                  fontSize: 13.5,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                              const TextSpan(
                                                text: ' /USDT',
                                                style: TextStyle(
                                                  color: _faint,
                                                  fontSize: 11.5,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          coinName(s),
                                          style: const TextStyle(
                                            color: _muted,
                                            fontSize: 10.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (active)
                                    Padding(
                                      padding: const EdgeInsets.only(right: 6),
                                      child: Icon(
                                        Icons.check_rounded,
                                        color: widget.accent,
                                        size: 18,
                                      ),
                                    ),
                                  IconButton(
                                    visualDensity: VisualDensity.compact,
                                    onPressed: () => Favorites.toggle(s),
                                    icon: Icon(
                                      fav
                                          ? Icons.star_rounded
                                          : Icons.star_border_rounded,
                                      color: fav ? widget.accent : _faint,
                                      size: 20,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

/* ================================================================
   Live market list (Favorites / Hot / Gainers / Losers)
   ================================================================ */

class _Mini {
  final double price;
  final double open;
  final double quoteVol;

  const _Mini(this.price, this.open, this.quoteVol);

  double get pct => open == 0 ? 0 : (price - open) / open * 100;
}

/// Live list of markets, updated every second from Binance.
class MarketList extends StatefulWidget {
  final List<String> symbols;
  final Color accent;
  final ValueChanged<String> onSelect;
  final Color upColor;
  final Color downColor;

  const MarketList({
    super.key,
    this.symbols = kMarketSymbols,
    this.accent = const Color(0xFFF0B90B),
    required this.onSelect,
    this.upColor = kUpColor,
    this.downColor = kDownColor,
  });

  @override
  State<MarketList> createState() => _MarketListState();
}

class _MarketListState extends State<MarketList> with WidgetsBindingObserver {
  static const List<String> _tabs = ['Favorites', 'Hot', 'Gainers', 'Losers'];

  late final BinanceStream _stream;
  final Dio _dio = Dio(
    BaseOptions(
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 15),
    ),
  );
  final Map<String, _Mini> _data = {};
  final Map<String, _Mini> _pending = {};
  Timer? _timer;
  int _tab = 1;
  bool _paused = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    Favorites.load();

    _stream = BinanceStream(onMessage: _onMessage);
    _stream.connect([
      for (final s in widget.symbols) '${s.toLowerCase()}@miniTicker',
    ]);
    _loadSnapshot();

    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted || _pending.isEmpty) return;
      setState(() {
        _data.addAll(_pending);
        _pending.clear();
      });
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused) {
      _paused = true;
      _stream.pause();
    }
    if (state == AppLifecycleState.resumed && _paused) {
      _paused = false;
      _stream.resume();
      _loadSnapshot();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _timer?.cancel();
    _stream.dispose();
    _dio.close(force: true);
    super.dispose();
  }

  /// One REST call so the list is full straight away.
  Future<void> _loadSnapshot() async {
    const hosts = ['https://data-api.binance.vision', 'https://api.binance.com'];
    for (final host in hosts) {
      try {
        final res = await _dio.get(
          '$host/api/v3/ticker/24hr',
          queryParameters: {'symbols': jsonEncode(widget.symbols)},
        );
        if (res.data is List) {
          if (!mounted) return;
          setState(() {
            for (final t in (res.data as List).whereType<Map>()) {
              final s = t['symbol']?.toString() ?? '';
              _data[s] = _Mini(
                toD(t['lastPrice']),
                toD(t['openPrice']),
                toD(t['quoteVolume']),
              );
            }
          });
          return;
        }
      } catch (_) {
        // Try the next host.
      }
    }
  }

  void _onMessage(String stream, Map<String, dynamic> data) {
    final s = data['s']?.toString();
    if (s == null) return;
    _pending[s] = _Mini(toD(data['c']), toD(data['o']), toD(data['q']));
  }

  List<String> _sorted(Set<String> favs) {
    final list = widget.symbols.where((s) => _data.containsKey(s)).toList();
    switch (_tab) {
      case 0:
        return widget.symbols.where((s) => favs.contains(s)).toList();
      case 2:
        list.sort((a, b) => _data[b]!.pct.compareTo(_data[a]!.pct));
        return list;
      case 3:
        list.sort((a, b) => _data[a]!.pct.compareTo(_data[b]!.pct));
        return list;
      default:
        list.sort((a, b) => _data[b]!.quoteVol.compareTo(_data[a]!.quoteVol));
        return list;
    }
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<Set<String>>(
      valueListenable: Favorites.notifier,
      builder: (context, favs, _) {
        final list = _sorted(favs);

        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  for (var i = 0; i < _tabs.length; i++)
                    GestureDetector(
                      onTap: () => setState(() => _tab = i),
                      child: Container(
                        margin: const EdgeInsets.only(right: 8),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: _tab == i ? _sheetBg : Colors.transparent,
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Text(
                          _tabs[i],
                          style: TextStyle(
                            color: _tab == i ? Colors.white : _muted,
                            fontSize: 12,
                            fontWeight:
                                _tab == i ? FontWeight.w800 : FontWeight.w600,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Name / Vol',
                      style: TextStyle(color: _faint, fontSize: 10.5),
                    ),
                  ),
                  Expanded(
                    child: Text(
                      'Last Price',
                      textAlign: TextAlign.right,
                      style: TextStyle(color: _faint, fontSize: 10.5),
                    ),
                  ),
                  SizedBox(width: 16),
                  SizedBox(
                    width: 76,
                    child: Text(
                      '24h Chg%',
                      textAlign: TextAlign.right,
                      style: TextStyle(color: _faint, fontSize: 10.5),
                    ),
                  ),
                ],
              ),
            ),
            if (list.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 28),
                child: Center(
                  child: Text(
                    _tab == 0
                        ? 'Tap the star on a coin to add it here.'
                        : 'Loading markets…',
                    style: const TextStyle(color: _faint, fontSize: 12),
                  ),
                ),
              ),
            for (final s in list) _row(s),
          ],
        );
      },
    );
  }

  Widget _row(String s) {
    final m = _data[s];
    final pct = m?.pct;
    final isUp = (pct ?? 0) >= 0;
    final decimals = decimalsFor(m?.price);

    return InkWell(
      onTap: () => widget.onSelect(s),
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 9),
        child: Row(
          children: [
            Expanded(
              child: Row(
                children: [
                  CoinIcon(symbol: s, size: 26),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text.rich(
                          TextSpan(
                            children: [
                              TextSpan(
                                text: baseOf(s),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const TextSpan(
                                text: ' /USDT',
                                style: TextStyle(color: _faint, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Vol ${fmtCompact(m?.quoteVol)}',
                          style: const TextStyle(color: _muted, fontSize: 10.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    fmtNum(m?.price, decimals),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '\$${fmtNum(m?.price, decimals)}',
                    style: const TextStyle(color: _muted, fontSize: 10.5),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 16),
            Container(
              width: 76,
              height: 30,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: pct == null
                    ? _border
                    : (isUp ? widget.upColor : widget.downColor),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                fmtPct(pct),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 12.5,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}