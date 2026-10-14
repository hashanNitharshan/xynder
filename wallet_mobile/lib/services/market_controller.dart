import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/widgets.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'binance_stream.dart';

export 'binance_stream.dart' show StreamStatus;

/* ================================================================
   Market list, names and intervals
   ================================================================ */

/// Markets shown in the app (all live from Binance). Same list as the website.
const List<String> kMarketSymbols = [
  'BTCUSDT',
  'ETHUSDT',
  'BNBUSDT',
  'SOLUSDT',
  'XRPUSDT',
  'TRXUSDT',
  'DOGEUSDT',
  'ADAUSDT',
  'TONUSDT',
  'LINKUSDT',
  'AVAXUSDT',
  'DOTUSDT',
  'LTCUSDT',
  'SHIBUSDT',
  'NEARUSDT',
  'UNIUSDT',
];

const Map<String, String> kCoinNames = {
  'BTC': 'Bitcoin',
  'ETH': 'Ethereum',
  'BNB': 'BNB',
  'SOL': 'Solana',
  'XRP': 'XRP',
  'TRX': 'TRON',
  'DOGE': 'Dogecoin',
  'ADA': 'Cardano',
  'TON': 'Toncoin',
  'LINK': 'Chainlink',
  'AVAX': 'Avalanche',
  'DOT': 'Polkadot',
  'LTC': 'Litecoin',
  'SHIB': 'Shiba Inu',
  'NEAR': 'NEAR Protocol',
  'UNI': 'Uniswap',
};

/// Intervals shown directly in the chart toolbar.
const List<String> kMainIntervals = ['1m', '15m', '1h', '4h', '1d'];

/// Intervals shown under "More".
const List<String> kMoreIntervals = [
  '1s',
  '3m',
  '5m',
  '30m',
  '2h',
  '6h',
  '8h',
  '12h',
  '3d',
  '1w',
  '1M',
];

// Market data only endpoint. Falls back to the main API if blocked.
const List<String> _restHosts = [
  'https://data-api.binance.vision',
  'https://api.binance.com',
];

String baseOf(String symbol) =>
    symbol.endsWith('USDT') ? symbol.substring(0, symbol.length - 4) : symbol;

String coinName(String symbol) => kCoinNames[baseOf(symbol)] ?? baseOf(symbol);

String coinIconUrl(String symbol) =>
    'https://cdn.jsdelivr.net/gh/spothq/cryptocurrency-icons@master/128/color/'
    '${baseOf(symbol).toLowerCase()}.png';

double toD(dynamic v) => double.tryParse(v?.toString() ?? '') ?? 0;

/* ================================================================
   Models
   ================================================================ */

class Candle {
  final int time; // open time in milliseconds
  final double open;
  final double high;
  final double low;
  final double close;
  final double volume;

  const Candle({
    required this.time,
    required this.open,
    required this.high,
    required this.low,
    required this.close,
    required this.volume,
  });

  /// REST array format: [openTime, open, high, low, close, volume, ...]
  factory Candle.fromRest(List k) => Candle(
        time: (k[0] as num).toInt(),
        open: toD(k[1]),
        high: toD(k[2]),
        low: toD(k[3]),
        close: toD(k[4]),
        volume: toD(k[5]),
      );

  /// WebSocket kline object format.
  factory Candle.fromWs(Map k) => Candle(
        time: (k['t'] as num).toInt(),
        open: toD(k['o']),
        high: toD(k['h']),
        low: toD(k['l']),
        close: toD(k['c']),
        volume: toD(k['v']),
      );

  bool get isUp => close >= open;
}

class BookLevel {
  final double price;
  final double qty;
  const BookLevel(this.price, this.qty);
}

class MarketTrade {
  final int id;
  final double price;
  final double qty;
  final int time;
  final bool buy;

  const MarketTrade({
    required this.id,
    required this.price,
    required this.qty,
    required this.time,
    required this.buy,
  });
}

class MarketStats {
  final double last;
  final double change;
  final double changePct;
  final double high;
  final double low;
  final double open;
  final double volBase;
  final double volQuote;
  final double weightedAvg;
  final int count;

  const MarketStats({
    required this.last,
    required this.change,
    required this.changePct,
    required this.high,
    required this.low,
    required this.open,
    required this.volBase,
    required this.volQuote,
    required this.weightedAvg,
    required this.count,
  });

  factory MarketStats.fromTicker(Map data) => MarketStats(
        last: toD(data['c']),
        change: toD(data['p']),
        changePct: toD(data['P']),
        high: toD(data['h']),
        low: toD(data['l']),
        open: toD(data['o']),
        volBase: toD(data['v']),
        volQuote: toD(data['q']),
        weightedAvg: toD(data['w']),
        count: (data['n'] as num?)?.toInt() ?? 0,
      );

  bool get isUp => changePct >= 0;
}

/* ================================================================
   Formatting
   ================================================================ */

int decimalsFor(double? price) {
  final p = (price ?? 0).abs();
  if (p == 0) return 2;
  if (p >= 1000) return 2;
  if (p >= 1) return 4;
  if (p >= 0.01) return 5;
  return 8;
}

String fmtNum(double? value, [int decimals = 2]) {
  if (value == null || !value.isFinite) return '--';
  final negative = value < 0;
  final parts = value.abs().toStringAsFixed(decimals).split('.');
  final whole = parts[0].replaceAllMapped(
    RegExp(r'\B(?=(\d{3})+(?!\d))'),
    (_) => ',',
  );
  final out = parts.length > 1 ? '$whole.${parts[1]}' : whole;
  return negative ? '-$out' : out;
}

String fmtCompact(double? value) {
  if (value == null || !value.isFinite) return '--';
  final v = value.abs();
  final sign = value < 0 ? '-' : '';
  if (v >= 1e9) return '$sign${(v / 1e9).toStringAsFixed(2)}B';
  if (v >= 1e6) return '$sign${(v / 1e6).toStringAsFixed(2)}M';
  if (v >= 1e3) return '$sign${(v / 1e3).toStringAsFixed(2)}K';
  return '$sign${v.toStringAsFixed(2)}';
}

String fmtPct(double? value) {
  if (value == null || !value.isFinite) return '--';
  return '${value >= 0 ? '+' : ''}${value.toStringAsFixed(2)}%';
}

String two(int n) => n.toString().padLeft(2, '0');

String clockTime(int ms) {
  final d = DateTime.fromMillisecondsSinceEpoch(ms);
  return '${two(d.hour)}:${two(d.minute)}:${two(d.second)}';
}

// DateTime.fromMillisecondsSinceEpoch gives the phone's local time,
// so Sri Lanka and India users see their own time.
String axisTime(int ms, String interval) {
  final d = DateTime.fromMillisecondsSinceEpoch(ms);
  switch (interval) {
    case '1s':
      return '${two(d.hour)}:${two(d.minute)}:${two(d.second)}';
    case '1m':
    case '3m':
    case '5m':
    case '15m':
    case '30m':
      return '${two(d.hour)}:${two(d.minute)}';
    case '1h':
    case '2h':
    case '4h':
    case '6h':
    case '8h':
    case '12h':
      return '${two(d.month)}-${two(d.day)} ${two(d.hour)}:${two(d.minute)}';
    case '1M':
      return '${d.year}-${two(d.month)}';
    default:
      return '${d.year % 100}-${two(d.month)}-${two(d.day)}';
  }
}

String fullTime(int ms, String interval) {
  final d = DateTime.fromMillisecondsSinceEpoch(ms);
  final date = '${d.year}-${two(d.month)}-${two(d.day)}';
  if (interval == '1d' || interval == '3d' || interval == '1w' || interval == '1M') {
    return date;
  }
  final time = interval == '1s'
      ? '${two(d.hour)}:${two(d.minute)}:${two(d.second)}'
      : '${two(d.hour)}:${two(d.minute)}';
  return '$date $time';
}

/* ================================================================
   Favorites (saved on the phone)
   ================================================================ */

class Favorites {
  static const String _key = 'bitxnow_favorite_markets';
  static final ValueNotifier<Set<String>> notifier =
      ValueNotifier<Set<String>>(<String>{});
  static bool _loaded = false;

  static Future<void> load() async {
    if (_loaded) return;
    _loaded = true;
    try {
      final prefs = await SharedPreferences.getInstance();
      notifier.value = (prefs.getStringList(_key) ?? const <String>[]).toSet();
    } catch (_) {
      // Favorites are optional.
    }
  }

  static bool isFavorite(String symbol) => notifier.value.contains(symbol);

  static Future<void> toggle(String symbol) async {
    final next = Set<String>.from(notifier.value);
    if (!next.remove(symbol)) next.add(symbol);
    notifier.value = next;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList(_key, next.toList());
    } catch (_) {
      // Ignore storage errors.
    }
  }
}

/* ================================================================
   Market controller
   ================================================================ */

/// Holds all live data for one market: candles, 24h stats, order book,
/// trades and performance. Data from Binance arrives many times per second,
/// so listeners are told about changes at most every 250 ms. This keeps
/// phones smooth.
class MarketController extends ChangeNotifier with WidgetsBindingObserver {
  MarketController({
    required String symbol,
    String interval = '15m',
    this.withBook = true,
    this.withPerformance = false,
    this.bookRows = 20,
    this.tradeRows = 40,
  })  : _symbol = symbol,
        _interval = interval {
    _stream = BinanceStream(
      onMessage: _onMessage,
      onStatus: (s) {
        _status = s;
        _dirty = true;
      },
    );
    _timer = Timer.periodic(
      const Duration(milliseconds: 250),
      (_) => _flush(),
    );
    WidgetsBinding.instance.addObserver(this);
    _start();
  }

  final bool withBook;
  final bool withPerformance;
  final int bookRows;
  final int tradeRows;

  static const int _maxCandles = 1500;

  late final BinanceStream _stream;
  Timer? _timer;
  final Dio _dio = Dio(
    BaseOptions(
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 15),
    ),
  );

  String _symbol;
  String _interval;
  List<Candle> _candles = [];
  List<Candle> _daily = [];
  MarketStats? _stats;
  List<BookLevel> _bids = [];
  List<BookLevel> _asks = [];
  List<MarketTrade> _trades = [];
  StreamStatus _status = StreamStatus.idle;
  bool _loading = true;
  bool _ready = false;
  String? _error;
  int _priceDir = 0;
  int _loadToken = 0;
  bool _paused = false;
  bool _disposed = false;

  // Buffers filled by the socket and pushed to listeners by the timer.
  List<BookLevel>? _pendingBids;
  List<BookLevel>? _pendingAsks;
  final List<MarketTrade> _pendingTrades = [];
  MarketStats? _pendingStats;
  bool _dirty = false;

  String get symbol => _symbol;
  String get interval => _interval;
  String get base => baseOf(_symbol);
  List<Candle> get candles => _candles;
  MarketStats? get stats => _stats;
  List<BookLevel> get bids => _bids;
  List<BookLevel> get asks => _asks;
  List<MarketTrade> get trades => _trades;
  StreamStatus get status => _status;
  bool get loading => _loading;
  String? get error => _error;
  int get priceDir => _priceDir;

  double? get lastPrice =>
      _stats?.last ?? (_candles.isNotEmpty ? _candles.last.close : null);

  int get decimals => decimalsFor(lastPrice);

  int get qtyDecimals => (lastPrice ?? 0) >= 100 ? 5 : 2;

  /// Price change for Today, 7 Days, 30 Days, 90 Days, 180 Days and 1 Year.
  List<MapEntry<String, double?>> get performance {
    final last = lastPrice;
    double? since(int daysBack) {
      if (last == null || _daily.length <= daysBack) return null;
      final ref = _daily[_daily.length - 1 - daysBack].close;
      if (ref == 0) return null;
      return (last - ref) / ref * 100;
    }

    double? today() {
      if (last == null || _daily.isEmpty || _daily.last.open == 0) return null;
      return (last - _daily.last.open) / _daily.last.open * 100;
    }

    return [
      MapEntry('Today', today()),
      MapEntry('7 Days', since(7)),
      MapEntry('30 Days', since(30)),
      MapEntry('90 Days', since(90)),
      MapEntry('180 Days', since(180)),
      MapEntry('1 Year', since(365)),
    ];
  }

  /// Pass notify: false when calling from didUpdateWidget, because the
  /// widget rebuilds anyway and listeners must not be told during a build.
  void setSymbol(String symbol, {bool notify = true}) {
    if (symbol == _symbol) return;
    _symbol = symbol;
    _start();
    if (notify) notifyListeners();
  }

  void setInterval(String interval) {
    if (interval == _interval) return;
    _interval = interval;
    _start(chartOnly: true);
    notifyListeners();
  }

  /// Load everything again (pull to refresh).
  Future<void> reload() async {
    _start();
    notifyListeners();
  }

  List<String> get _streamNames {
    final s = _symbol.toLowerCase();
    return [
      '$s@kline_$_interval',
      '$s@ticker',
      if (withBook) '$s@depth20@100ms',
      if (withBook) '$s@aggTrade',
    ];
  }

  void _start({bool chartOnly = false}) {
    _ready = false;
    _candles = [];
    _loading = true;
    _error = null;

    if (!chartOnly) {
      _daily = [];
      _bids = [];
      _asks = [];
      _trades = [];
      _stats = null;
      _priceDir = 0;
      _pendingBids = null;
      _pendingAsks = null;
      _pendingTrades.clear();
      _pendingStats = null;
      if (withPerformance) _loadDaily();
    }

    _paused = false;
    _stream.connect(_streamNames);
    _loadHistory();
  }

  Future<List?> _getKlines(String symbol, String interval, int limit) async {
    for (final host in _restHosts) {
      try {
        final res = await _dio.get(
          '$host/api/v3/klines',
          queryParameters: {
            'symbol': symbol,
            'interval': interval,
            'limit': limit,
          },
        );
        if (res.data is List) return res.data as List;
      } catch (_) {
        // Try the next host.
      }
    }
    return null;
  }

  Future<void> _loadHistory() async {
    final token = ++_loadToken;
    final raw = await _getKlines(_symbol, _interval, 500);
    if (_disposed || token != _loadToken) return;

    if (raw != null) {
      _candles = raw.whereType<List>().map(Candle.fromRest).toList();
      _error = null;
    } else {
      _error = 'Could not load chart history. Live updates will continue.';
    }
    _ready = true;
    _loading = false;
    notifyListeners();
  }

  Future<void> _loadDaily() async {
    final symbol = _symbol;
    final raw = await _getKlines(symbol, '1d', 400);
    if (_disposed || symbol != _symbol || raw == null) return;
    _daily = raw.whereType<List>().map(Candle.fromRest).toList();
    _dirty = true;
  }

  List<BookLevel> _levels(dynamic list) {
    if (list is! List) return [];
    return list
        .whereType<List>()
        .take(bookRows)
        .map((e) => BookLevel(toD(e[0]), toD(e[1])))
        .toList();
  }

  void _onMessage(String stream, Map<String, dynamic> data) {
    if (stream.endsWith('@kline_$_interval')) {
      if (!_ready) return;
      final k = data['k'];
      if (k is! Map) return;

      final c = Candle.fromWs(k);
      if (_candles.isNotEmpty) {
        final last = _candles.last;
        if (c.time < last.time) return;
        if (c.time == last.time) {
          _candles[_candles.length - 1] = c;
        } else {
          _candles.add(c);
        }
      } else {
        _candles.add(c);
      }

      if (_candles.length > _maxCandles) {
        _candles.removeRange(0, _candles.length - _maxCandles);
      }
      _dirty = true;
      return;
    }

    if (stream.endsWith('@ticker')) {
      _pendingStats = MarketStats.fromTicker(data);
      return;
    }

    if (stream.contains('@depth')) {
      _pendingBids = _levels(data['bids']);
      _pendingAsks = _levels(data['asks']);
      return;
    }

    if (stream.endsWith('@aggTrade')) {
      _pendingTrades.insert(
        0,
        MarketTrade(
          id: (data['a'] as num?)?.toInt() ?? 0,
          price: toD(data['p']),
          qty: toD(data['q']),
          time: (data['T'] as num?)?.toInt() ?? 0,
          // m = true means the buyer was the maker, so the taker sold.
          buy: data['m'] != true,
        ),
      );
      if (_pendingTrades.length > tradeRows) {
        _pendingTrades.removeRange(tradeRows, _pendingTrades.length);
      }
    }
  }

  void _flush() {
    if (_disposed) return;
    var changed = _dirty;

    if (_pendingBids != null) {
      _bids = _pendingBids!;
      _asks = _pendingAsks ?? [];
      _pendingBids = null;
      _pendingAsks = null;
      changed = true;
    }

    if (_pendingTrades.isNotEmpty) {
      final fresh = List<MarketTrade>.from(_pendingTrades);
      _pendingTrades.clear();
      _trades = [...fresh, ..._trades].take(tradeRows).toList();
      changed = true;
    }

    if (_pendingStats != null) {
      final oldPrice = _stats?.last;
      final newPrice = _pendingStats!.last;
      if (oldPrice != null && oldPrice != newPrice) {
        _priceDir = newPrice > oldPrice ? 1 : -1;
      }
      _stats = _pendingStats;
      _pendingStats = null;
      changed = true;
    }

    if (changed) {
      _dirty = false;
      notifyListeners();
    }
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused) {
      _paused = true;
      _stream.pause();
    } else if (state == AppLifecycleState.resumed && _paused) {
      // Reload history so there is no gap after the app slept.
      _start();
      notifyListeners();
    }
  }

  @override
  void dispose() {
    _disposed = true;
    WidgetsBinding.instance.removeObserver(this);
    _timer?.cancel();
    _stream.dispose();
    _dio.close(force: true);
    super.dispose();
  }
}