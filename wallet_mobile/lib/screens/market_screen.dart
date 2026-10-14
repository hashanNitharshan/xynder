import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../services/market_controller.dart';
import '../widgets/candle_chart.dart';
import '../widgets/market_widgets.dart';

const Color _pageBg = Color(0xFF0B0E11);
const Color _surface = Color(0xFF181A20);
const Color _card = Color(0xFF1E2329);
const Color _border = Color(0xFF2B3139);
const Color _text = Color(0xFFEAECEF);
const Color _muted = Color(0xFF848E9C);
const Color _faint = Color(0xFF5E6673);

/// Full Binance style market page: price header, candlestick chart with
/// indicators, performance, order book, market trades and coin info,
/// with Buy and Sell buttons fixed at the bottom.
///
/// Set [embedded] to true when the page is shown inside the dashboard as
/// the Trade tab. Then there is no back button and Buy / Sell do not
/// close the page.
class MarketScreen extends StatefulWidget {
  final String symbol;
  final List<String> symbols;
  final Color accent;
  final VoidCallback? onBuy;
  final VoidCallback? onSell;
  final bool embedded;
  final ValueChanged<String>? onSymbolChange;

  const MarketScreen({
    super.key,
    this.symbol = 'BTCUSDT',
    this.symbols = kMarketSymbols,
    this.accent = const Color(0xFFF0B90B),
    this.onBuy,
    this.onSell,
    this.embedded = false,
    this.onSymbolChange,
  });

  @override
  State<MarketScreen> createState() => _MarketScreenState();
}

class _MarketScreenState extends State<MarketScreen> {
  late final MarketController _c;

  MainIndicator _main = MainIndicator.ma;
  SubIndicator _sub = SubIndicator.vol;
  bool _large = false;
  bool _redUp = false;
  int _tab = 0;

  Color get _up => _redUp ? kDownColor : kUpColor;
  Color get _down => _redUp ? kUpColor : kDownColor;

  @override
  void initState() {
    super.initState();
    Favorites.load();
    _c = MarketController(
      symbol: widget.symbol,
      interval: '15m',
      withBook: true,
      withPerformance: true,
    );
  }

  @override
  void didUpdateWidget(covariant MarketScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.symbol != widget.symbol && widget.symbol != _c.symbol) {
      _c.setSymbol(widget.symbol, notify: false);
    }
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  Future<void> _pickPair() async {
    final picked = await showPairPicker(
      context,
      current: _c.symbol,
      symbols: widget.symbols,
      accent: widget.accent,
    );
    if (picked != null && mounted) {
      _c.setSymbol(picked);
      widget.onSymbolChange?.call(picked);
    }
  }

  void _buy() {
    if (!widget.embedded) Navigator.pop(context);
    widget.onBuy?.call();
  }

  void _sell() {
    if (!widget.embedded) Navigator.pop(context);
    widget.onSell?.call();
  }

  /* ================================================================
     Page
     ================================================================ */

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _pageBg,
      appBar: _appBar(),
      body: AnimatedBuilder(
        animation: _c,
        builder: (context, _) {
          return RefreshIndicator(
            onRefresh: _c.reload,
            color: widget.accent,
            backgroundColor: _card,
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(
                parent: BouncingScrollPhysics(),
              ),
              padding: EdgeInsets.zero,
              children: [
                _header(),
                const SizedBox(height: 10),
                _toolbar(),
                CandleChart(
                  controller: _c,
                  main: _main,
                  sub: _sub,
                  height: _large ? 540 : 400,
                  upColor: _up,
                  downColor: _down,
                ),
                if (_c.error != null)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(14, 4, 14, 0),
                    child: Text(
                      _c.error!,
                      style: TextStyle(color: _down, fontSize: 11),
                    ),
                  ),
                _indicatorBar(),
                _performance(),
                Container(height: 8, color: _surface),
                _tabs(),
                Padding(
                  padding: const EdgeInsets.fromLTRB(14, 10, 14, 0),
                  child: _tab == 0
                      ? _orderBook()
                      : _tab == 1
                          ? _tradesView()
                          : _infoView(),
                ),
                const SizedBox(height: 24),
              ],
            ),
          );
        },
      ),
      bottomNavigationBar: _bottomBar(),
    );
  }

  PreferredSizeWidget _appBar() {
    return AppBar(
      backgroundColor: _pageBg,
      surfaceTintColor: _pageBg,
      elevation: 0,
      automaticallyImplyLeading: false,
      titleSpacing: widget.embedded ? 14 : 0,
      leading: widget.embedded
          ? null
          : IconButton(
              onPressed: () => Navigator.maybePop(context),
              icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
            ),
      title: AnimatedBuilder(
        animation: _c,
        builder: (context, _) => GestureDetector(
          onTap: _pickPair,
          behavior: HitTestBehavior.opaque,
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.swap_horiz_rounded, color: _text, size: 20),
              const SizedBox(width: 8),
              CoinIcon(symbol: _c.symbol, size: 20),
              const SizedBox(width: 6),
              Text(
                '${_c.base}/USDT',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const Icon(Icons.arrow_drop_down_rounded, color: _muted, size: 22),
            ],
          ),
        ),
      ),
      actions: [
        AnimatedBuilder(
          animation: _c,
          builder: (context, _) => _statusDot(),
        ),
        ValueListenableBuilder<Set<String>>(
          valueListenable: Favorites.notifier,
          builder: (context, favs, _) => AnimatedBuilder(
            animation: _c,
            builder: (context, _) {
              final fav = favs.contains(_c.symbol);
              return IconButton(
                onPressed: () => Favorites.toggle(_c.symbol),
                icon: Icon(
                  fav ? Icons.star_rounded : Icons.star_border_rounded,
                  color: fav ? widget.accent : _text,
                ),
              );
            },
          ),
        ),
        IconButton(
          onPressed: _openSettings,
          icon: const Icon(Icons.tune_rounded, color: _text, size: 21),
        ),
      ],
    );
  }

  Widget _statusDot() {
    final s = _c.status;
    final color = s == StreamStatus.live
        ? _up
        : s == StreamStatus.reconnecting
            ? _down
            : _muted;
    final label = s == StreamStatus.live
        ? 'Live'
        : s == StreamStatus.reconnecting
            ? 'Reconnecting'
            : 'Connecting';
    return Padding(
      padding: const EdgeInsets.only(right: 2),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 6,
            height: 6,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 10,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }

  /* ================================================================
     Price header
     ================================================================ */

  Widget _header() {
    final st = _c.stats;
    final d = _c.decimals;
    final last = _c.lastPrice;
    final isUp = st?.isUp ?? true;
    final priceColor = _c.priceDir > 0
        ? _up
        : _c.priceDir < 0
            ? _down
            : (isUp ? _up : _down);

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 6, 14, 0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 11,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: Alignment.centerLeft,
                  child: Text(
                    fmtNum(last, d),
                    style: TextStyle(
                      color: priceColor,
                      fontSize: 30,
                      height: 1.1,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.5,
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text.rich(
                  TextSpan(
                    children: [
                      TextSpan(
                        text: '≈\$${fmtNum(last, d)}  ',
                        style: const TextStyle(color: _text, fontSize: 12),
                      ),
                      TextSpan(
                        text: st == null ? '--' : fmtPct(st.changePct),
                        style: TextStyle(
                          color: isUp ? _up : _down,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: widget.accent.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    coinName(_c.symbol),
                    style: TextStyle(
                      color: widget.accent,
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            flex: 10,
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(child: _stat('24h High', fmtNum(st?.high, d))),
                    Expanded(
                      child: _stat('24h Vol(${_c.base})', fmtCompact(st?.volBase)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(child: _stat('24h Low', fmtNum(st?.low, d))),
                    Expanded(
                      child: _stat('24h Vol(USDT)', fmtCompact(st?.volQuote)),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _stat(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(color: _faint, fontSize: 10),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: _text,
            fontSize: 11,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }

  /* ================================================================
     Interval toolbar
     ================================================================ */

  Widget _toolbar() {
    final current = _c.interval;
    final inMore = kMoreIntervals.contains(current);

    Widget chip(String label, bool active, VoidCallback onTap, {bool caret = false}) {
      return GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Container(
          margin: const EdgeInsets.only(right: 4),
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
          decoration: BoxDecoration(
            color: active ? _card : Colors.transparent,
            borderRadius: BorderRadius.circular(6),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: active ? widget.accent : _muted,
                  fontSize: 12,
                  fontWeight: active ? FontWeight.w800 : FontWeight.w600,
                ),
              ),
              if (caret)
                Icon(
                  Icons.arrow_drop_down_rounded,
                  size: 16,
                  color: active ? widget.accent : _muted,
                ),
            ],
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.fromLTRB(10, 4, 4, 4),
      decoration: const BoxDecoration(
        border: Border(
          top: BorderSide(color: _border, width: 0.6),
          bottom: BorderSide(color: _border, width: 0.6),
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  const Padding(
                    padding: EdgeInsets.only(right: 6),
                    child: Text(
                      'Time',
                      style: TextStyle(color: _faint, fontSize: 11.5),
                    ),
                  ),
                  for (final iv in kMainIntervals)
                    chip(iv, iv == current, () => _c.setInterval(iv)),
                  chip(
                    inMore ? current : 'More',
                    inMore,
                    _openMoreIntervals,
                    caret: true,
                  ),
                ],
              ),
            ),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            tooltip: _large ? 'Smaller chart' : 'Larger chart',
            onPressed: () => setState(() => _large = !_large),
            icon: Icon(
              _large ? Icons.close_fullscreen_rounded : Icons.open_in_full_rounded,
              color: _muted,
              size: 17,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _openMoreIntervals() async {
    final picked = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: _card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (context) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Time Interval',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 14),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  for (final iv in [...kMainIntervals, ...kMoreIntervals])
                    GestureDetector(
                      onTap: () => Navigator.pop(context, iv),
                      child: Container(
                        width: 64,
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: iv == _c.interval
                              ? widget.accent.withValues(alpha: 0.15)
                              : _pageBg,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: iv == _c.interval ? widget.accent : _border,
                          ),
                        ),
                        child: Text(
                          iv,
                          style: TextStyle(
                            color: iv == _c.interval ? widget.accent : _text,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
    if (picked != null && mounted) _c.setInterval(picked);
  }

  /* ================================================================
     Indicator bar
     ================================================================ */

  Widget _indicatorBar() {
    Widget item(String label, bool active, VoidCallback onTap) {
      return GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
          child: Text(
            label,
            style: TextStyle(
              color: active ? Colors.white : _muted,
              fontSize: 12,
              fontWeight: active ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
        ),
      );
    }

    void setMain(MainIndicator m) =>
        setState(() => _main = _main == m ? MainIndicator.none : m);
    void setSub(SubIndicator s) =>
        setState(() => _sub = _sub == s ? SubIndicator.none : s);

    return Container(
      decoration: const BoxDecoration(
        border: Border(
          top: BorderSide(color: _border, width: 0.6),
          bottom: BorderSide(color: _border, width: 0.6),
        ),
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 4),
        child: Row(
          children: [
            item('MA', _main == MainIndicator.ma, () => setMain(MainIndicator.ma)),
            item('EMA', _main == MainIndicator.ema,
                () => setMain(MainIndicator.ema)),
            item('BOLL', _main == MainIndicator.boll,
                () => setMain(MainIndicator.boll)),
            Container(
              width: 1,
              height: 14,
              margin: const EdgeInsets.symmetric(horizontal: 6),
              color: _border,
            ),
            item('VOL', _sub == SubIndicator.vol, () => setSub(SubIndicator.vol)),
            item('MACD', _sub == SubIndicator.macd,
                () => setSub(SubIndicator.macd)),
            item('RSI', _sub == SubIndicator.rsi, () => setSub(SubIndicator.rsi)),
            item('KDJ', _sub == SubIndicator.kdj, () => setSub(SubIndicator.kdj)),
          ],
        ),
      ),
    );
  }

  /* ================================================================
     Performance (Today, 7 Days, ... 1 Year)
     ================================================================ */

  Widget _performance() {
    final items = _c.performance;
    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
      child: Row(
        children: [
          for (final e in items)
            Expanded(
              child: Column(
                children: [
                  Text(
                    e.key,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: _faint, fontSize: 10),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    fmtPct(e.value),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: e.value == null
                          ? _muted
                          : (e.value! >= 0 ? _up : _down),
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  /* ================================================================
     Tabs: Order Book / Trades / Info
     ================================================================ */

  Widget _tabs() {
    const labels = ['Order Book', 'Trades', 'Info'];
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 0),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: _border, width: 0.6)),
      ),
      child: Row(
        children: [
          for (var i = 0; i < labels.length; i++)
            GestureDetector(
              onTap: () => setState(() => _tab = i),
              behavior: HitTestBehavior.opaque,
              child: Padding(
                padding: const EdgeInsets.only(right: 22),
                child: Column(
                  children: [
                    Text(
                      labels[i],
                      style: TextStyle(
                        color: _tab == i ? Colors.white : _muted,
                        fontSize: 14,
                        fontWeight: _tab == i ? FontWeight.w800 : FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: 18,
                      height: 3,
                      decoration: BoxDecoration(
                        color: _tab == i ? widget.accent : Colors.transparent,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _orderBook() {
    final bids = _c.bids;
    final asks = _c.asks;
    final d = _c.decimals;
    final qd = _c.qtyDecimals;

    if (bids.isEmpty && asks.isEmpty) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 40),
        child: Center(
          child: Text(
            'Waiting for order book…',
            style: TextStyle(color: _faint, fontSize: 12),
          ),
        ),
      );
    }

    var bidSum = 0.0;
    var askSum = 0.0;
    var maxQty = 0.0;
    for (final l in bids) {
      bidSum += l.qty;
      maxQty = math.max(maxQty, l.qty);
    }
    for (final l in asks) {
      askSum += l.qty;
      maxQty = math.max(maxQty, l.qty);
    }
    if (maxQty <= 0) maxQty = 1;
    final total = bidSum + askSum;
    final bidPct = total == 0 ? 50.0 : bidSum / total * 100;

    Widget cell(BookLevel? l, bool bid) {
      if (l == null) return const SizedBox(height: 22);
      final color = bid ? _up : _down;
      final priceText = Text(
        fmtNum(l.price, d),
        style: TextStyle(
          color: color,
          fontSize: 11.5,
          fontWeight: FontWeight.w600,
        ),
      );
      final qtyText = Expanded(
        child: Text(
          fmtNum(l.qty, qd),
          textAlign: bid ? TextAlign.left : TextAlign.right,
          style: const TextStyle(color: _text, fontSize: 11.5),
        ),
      );
      return SizedBox(
        height: 22,
        child: Stack(
          children: [
            Align(
              alignment: bid ? Alignment.centerRight : Alignment.centerLeft,
              child: FractionallySizedBox(
                widthFactor: (l.qty / maxQty).clamp(0.0, 1.0),
                heightFactor: 1,
                child: Container(color: color.withValues(alpha: 0.12)),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 3),
              child: Row(
                children: bid ? [qtyText, priceText] : [priceText, qtyText],
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // Buy / sell pressure bar
        Row(
          children: [
            Text(
              'B ${bidPct.toStringAsFixed(0)}%',
              style: TextStyle(
                color: _up,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(width: 6),
            Expanded(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(2),
                child: SizedBox(
                  height: 4,
                  child: Row(
                    children: [
                      Expanded(
                        flex: math.max(1, bidPct.round()),
                        child: Container(color: _up),
                      ),
                      const SizedBox(width: 2),
                      Expanded(
                        flex: math.max(1, (100 - bidPct).round()),
                        child: Container(color: _down),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 6),
            Text(
              '${(100 - bidPct).toStringAsFixed(0)}% S',
              style: TextStyle(
                color: _down,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Bid  Amount(${_c.base})',
                      style: const TextStyle(color: _faint, fontSize: 10.5),
                    ),
                  ),
                  const Text(
                    'Price',
                    style: TextStyle(color: _faint, fontSize: 10.5),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 6),
            Expanded(
              child: Row(
                children: [
                  const Text(
                    'Price',
                    style: TextStyle(color: _faint, fontSize: 10.5),
                  ),
                  Expanded(
                    child: Text(
                      'Amount(${_c.base})  Ask',
                      textAlign: TextAlign.right,
                      style: const TextStyle(color: _faint, fontSize: 10.5),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        for (var i = 0; i < _c.bookRows; i++)
          Padding(
            padding: const EdgeInsets.only(bottom: 1),
            child: Row(
              children: [
                Expanded(child: cell(i < bids.length ? bids[i] : null, true)),
                const SizedBox(width: 6),
                Expanded(child: cell(i < asks.length ? asks[i] : null, false)),
              ],
            ),
          ),
      ],
    );
  }

  Widget _tradesView() {
    final trades = _c.trades;
    final d = _c.decimals;
    final qd = _c.qtyDecimals;

    return Column(
      children: [
        Row(
          children: [
            const Expanded(
              child: Text(
                'Time',
                style: TextStyle(color: _faint, fontSize: 10.5),
              ),
            ),
            const Expanded(
              child: Text(
                'Price(USDT)',
                textAlign: TextAlign.center,
                style: TextStyle(color: _faint, fontSize: 10.5),
              ),
            ),
            Expanded(
              child: Text(
                'Amount(${_c.base})',
                textAlign: TextAlign.right,
                style: const TextStyle(color: _faint, fontSize: 10.5),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        if (trades.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 40),
            child: Text(
              'Waiting for trades…',
              style: TextStyle(color: _faint, fontSize: 12),
            ),
          ),
        for (final t in trades)
          SizedBox(
            height: 22,
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    clockTime(t.time),
                    style: const TextStyle(color: _muted, fontSize: 11.5),
                  ),
                ),
                Expanded(
                  child: Text(
                    fmtNum(t.price, d),
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: t.buy ? _up : _down,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    fmtNum(t.qty, qd),
                    textAlign: TextAlign.right,
                    style: const TextStyle(color: _text, fontSize: 11.5),
                  ),
                ),
              ],
            ),
          ),
      ],
    );
  }

  Widget _infoView() {
    final st = _c.stats;
    final d = _c.decimals;

    Widget row(String label, String value, {Color color = _text}) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 9),
        child: Row(
          children: [
            Expanded(
              child: Text(
                label,
                style: const TextStyle(color: _muted, fontSize: 12),
              ),
            ),
            Text(
              value,
              style: TextStyle(
                color: color,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      );
    }

    final isUp = st?.isUp ?? true;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            CoinIcon(symbol: _c.symbol, size: 36),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  coinName(_c.symbol),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${_c.base} / USDT  ·  Spot',
                  style: const TextStyle(color: _muted, fontSize: 11),
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 8),
        row('Last Price', fmtNum(st?.last, d)),
        row(
          '24h Change',
          st == null ? '--' : '${fmtNum(st.change, d)}  (${fmtPct(st.changePct)})',
          color: isUp ? _up : _down,
        ),
        row('24h Open', fmtNum(st?.open, d)),
        row('24h High', fmtNum(st?.high, d)),
        row('24h Low', fmtNum(st?.low, d)),
        row('Weighted Avg Price', fmtNum(st?.weightedAvg, d)),
        row('24h Volume(${_c.base})', fmtCompact(st?.volBase)),
        row('24h Volume(USDT)', fmtCompact(st?.volQuote)),
        row('24h Trades', st == null ? '--' : fmtNum(st.count.toDouble(), 0)),
        row('Data Source', 'Binance Spot (live)'),
      ],
    );
  }

  /* ================================================================
     Bottom Buy / Sell bar
     ================================================================ */

  Widget _bottomBar() {
    return Container(
      decoration: const BoxDecoration(
        color: _surface,
        border: Border(top: BorderSide(color: _border, width: 0.6)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
          child: AnimatedBuilder(
            animation: _c,
            builder: (context, _) => Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 44,
                    child: ElevatedButton(
                      onPressed: _buy,
                      style: ElevatedButton.styleFrom(
                        elevation: 0,
                        backgroundColor: _up,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: Text(
                        'Buy ${_c.base}',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: SizedBox(
                    height: 44,
                    child: ElevatedButton(
                      onPressed: _sell,
                      style: ElevatedButton.styleFrom(
                        elevation: 0,
                        backgroundColor: _down,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: Text(
                        'Sell ${_c.base}',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  /* ================================================================
     Chart settings sheet
     ================================================================ */

  void _openSettings() {
    showModalBottomSheet<void>(
      context: context,
      backgroundColor: _card,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheet) {
          void update(VoidCallback fn) {
            setState(fn);
            setSheet(() {});
          }

          Widget option(String label, bool active, VoidCallback onTap) {
            return GestureDetector(
              onTap: onTap,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: active
                      ? widget.accent.withValues(alpha: 0.15)
                      : _pageBg,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: active ? widget.accent : _border,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (active)
                      Padding(
                        padding: const EdgeInsets.only(right: 4),
                        child: Icon(
                          Icons.check_rounded,
                          size: 14,
                          color: widget.accent,
                        ),
                      ),
                    Text(
                      label,
                      style: TextStyle(
                        color: active ? widget.accent : _text,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }

          Widget section(String title, List<Widget> children) {
            return Padding(
              padding: const EdgeInsets.only(top: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: _muted,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Wrap(spacing: 8, runSpacing: 8, children: children),
                ],
              ),
            );
          }

          Widget dot(Color c) => Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(color: c, shape: BoxShape.circle),
              );

          return SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 18),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Expanded(
                        child: Text(
                          'Chart Settings',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.close_rounded, color: _text),
                      ),
                    ],
                  ),
                  section('Candle colors', [
                    option('Green up · Red down', !_redUp,
                        () => update(() => _redUp = false)),
                    option('Red up · Green down', _redUp,
                        () => update(() => _redUp = true)),
                  ]),
                  Padding(
                    padding: const EdgeInsets.only(top: 10),
                    child: Row(
                      children: [
                        const Text(
                          'Up candle ',
                          style: TextStyle(color: _muted, fontSize: 11.5),
                        ),
                        dot(_up),
                        const SizedBox(width: 16),
                        const Text(
                          'Down candle ',
                          style: TextStyle(color: _muted, fontSize: 11.5),
                        ),
                        dot(_down),
                      ],
                    ),
                  ),
                  section('Main indicator', [
                    option('None', _main == MainIndicator.none,
                        () => update(() => _main = MainIndicator.none)),
                    option('MA', _main == MainIndicator.ma,
                        () => update(() => _main = MainIndicator.ma)),
                    option('EMA', _main == MainIndicator.ema,
                        () => update(() => _main = MainIndicator.ema)),
                    option('BOLL', _main == MainIndicator.boll,
                        () => update(() => _main = MainIndicator.boll)),
                  ]),
                  section('Lower panel', [
                    option('None', _sub == SubIndicator.none,
                        () => update(() => _sub = SubIndicator.none)),
                    option('VOL', _sub == SubIndicator.vol,
                        () => update(() => _sub = SubIndicator.vol)),
                    option('MACD', _sub == SubIndicator.macd,
                        () => update(() => _sub = SubIndicator.macd)),
                    option('RSI', _sub == SubIndicator.rsi,
                        () => update(() => _sub = SubIndicator.rsi)),
                    option('KDJ', _sub == SubIndicator.kdj,
                        () => update(() => _sub = SubIndicator.kdj)),
                  ]),
                  section('Chart size', [
                    option('Normal', !_large, () => update(() => _large = false)),
                    option('Large', _large, () => update(() => _large = true)),
                  ]),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}