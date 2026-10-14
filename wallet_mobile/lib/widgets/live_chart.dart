import 'package:flutter/material.dart';

import '../services/market_controller.dart';
import 'candle_chart.dart';
import 'market_widgets.dart';

const Color _cardBg = Color(0xFF0B0E11);
const Color _border = Color(0xFF2B3139);
const Color _chip = Color(0xFF1E2329);
const Color _text = Color(0xFFEAECEF);
const Color _muted = Color(0xFF848E9C);
const Color _faint = Color(0xFF5E6673);

/// Compact live chart card for the dashboard. Tap "Full chart" to open the
/// complete market page.
class LiveChart extends StatefulWidget {
  final String symbol;
  final List<String> symbols;
  final ValueChanged<String>? onSymbolChange;
  final VoidCallback? onTrade;
  final VoidCallback? onOpenFull;
  final Color accent;
  final double chartHeight;

  const LiveChart({
    super.key,
    this.symbol = 'BTCUSDT',
    this.symbols = kMarketSymbols,
    this.onSymbolChange,
    this.onTrade,
    this.onOpenFull,
    this.accent = const Color(0xFFF0B90B),
    this.chartHeight = 280,
  });

  /// Opens the coin picker. Kept here so older code still works.
  static Future<String?> pickSymbol(
    BuildContext context, {
    required String current,
    required List<String> symbols,
    Color accent = const Color(0xFFF0B90B),
  }) {
    return showPairPicker(
      context,
      current: current,
      symbols: symbols,
      accent: accent,
    );
  }

  @override
  State<LiveChart> createState() => _LiveChartState();
}

class _LiveChartState extends State<LiveChart> {
  late final MarketController _c;

  @override
  void initState() {
    super.initState();
    _c = MarketController(
      symbol: widget.symbol,
      interval: '15m',
      withBook: false,
    );
  }

  @override
  void didUpdateWidget(covariant LiveChart oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.symbol != widget.symbol) {
      _c.setSymbol(widget.symbol, notify: false);
    }
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  Future<void> _openPicker() async {
    final picked = await showPairPicker(
      context,
      current: _c.symbol,
      symbols: widget.symbols,
      accent: widget.accent,
    );
    if (picked == null || !mounted) return;
    if (widget.onSymbolChange != null) {
      widget.onSymbolChange!(picked);
    } else {
      _c.setSymbol(picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: _cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _border, width: 0.8),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          AnimatedBuilder(
            animation: _c,
            builder: (context, _) => Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _header(),
                _intervals(),
              ],
            ),
          ),
          CandleChart(
            controller: _c,
            main: MainIndicator.ma,
            sub: SubIndicator.vol,
            height: widget.chartHeight,
          ),
          _footer(),
        ],
      ),
    );
  }

  Widget _header() {
    final st = _c.stats;
    final d = _c.decimals;
    final isUp = st?.isUp ?? true;
    final priceColor = _c.priceDir > 0
        ? kUpColor
        : _c.priceDir < 0
            ? kDownColor
            : (isUp ? kUpColor : kDownColor);

    final live = _c.status == StreamStatus.live;

    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                GestureDetector(
                  onTap: _openPicker,
                  behavior: HitTestBehavior.opaque,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CoinIcon(symbol: _c.symbol, size: 20),
                      const SizedBox(width: 6),
                      Text(
                        '${_c.base}/USDT',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const Icon(
                        Icons.arrow_drop_down_rounded,
                        color: _muted,
                        size: 20,
                      ),
                      const SizedBox(width: 4),
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          color: live ? kUpColor : _muted,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 6),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: Alignment.centerLeft,
                  child: Text(
                    fmtNum(_c.lastPrice, d),
                    style: TextStyle(
                      color: priceColor,
                      fontSize: 24,
                      height: 1.1,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  st == null ? '--' : '${fmtNum(st.change, d)}  ${fmtPct(st.changePct)}',
                  style: TextStyle(
                    color: isUp ? kUpColor : kDownColor,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          SizedBox(
            width: 132,
            child: Column(
              children: [
                _stat('24h High', fmtNum(st?.high, d)),
                _stat('24h Low', fmtNum(st?.low, d)),
                _stat('24h Vol(USDT)', fmtCompact(st?.volQuote)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _stat(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 5),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: _faint, fontSize: 10),
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              color: _text,
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _intervals() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: const BoxDecoration(
        border: Border(
          top: BorderSide(color: _border, width: 0.6),
          bottom: BorderSide(color: _border, width: 0.6),
        ),
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            for (final iv in ['1s', ...kMainIntervals])
              GestureDetector(
                onTap: () => _c.setInterval(iv),
                child: Container(
                  margin: const EdgeInsets.only(right: 4),
                  padding:
                      const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                  decoration: BoxDecoration(
                    color: iv == _c.interval ? _chip : Colors.transparent,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    iv,
                    style: TextStyle(
                      color: iv == _c.interval ? widget.accent : _muted,
                      fontSize: 11.5,
                      fontWeight: iv == _c.interval
                          ? FontWeight.w800
                          : FontWeight.w600,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _footer() {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: _border, width: 0.6)),
      ),
      child: Row(
        children: [
          Expanded(
            child: SizedBox(
              height: 36,
              child: OutlinedButton.icon(
                onPressed: widget.onOpenFull,
                style: OutlinedButton.styleFrom(
                  foregroundColor: _text,
                  side: const BorderSide(color: _border),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.candlestick_chart_outlined, size: 16),
                label: const Text(
                  'Full chart',
                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700),
                ),
              ),
            ),
          ),
          if (widget.onTrade != null) ...[
            const SizedBox(width: 10),
            Expanded(
              child: SizedBox(
                height: 36,
                child: ElevatedButton(
                  onPressed: widget.onTrade,
                  style: ElevatedButton.styleFrom(
                    elevation: 0,
                    backgroundColor: widget.accent,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: AnimatedBuilder(
                    animation: _c,
                    builder: (context, _) => Text(
                      'Trade ${_c.base}',
                      style: const TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}