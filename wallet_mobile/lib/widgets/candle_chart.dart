import 'dart:math' as math;

import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';

import '../services/market_controller.dart';

/// Indicators drawn on top of the candles.
enum MainIndicator { none, ma, ema, boll }

/// Indicators drawn in the lower panel.
enum SubIndicator { none, vol, macd, rsi, kdj }

const Color kUpColor = Color(0xFF0ECB81);
const Color kDownColor = Color(0xFFF6465D);

const Color _border = Color(0xFF2B3139);
const Color _muted = Color(0xFF848E9C);
const Color _text = Color(0xFFEAECEF);
const Color _labelBg = Color(0xFF2B3139);
const Color _boxBg = Color(0xF21E2329);

const Color _line1 = Color(0xFFF0B90B);
const Color _line2 = Color(0xFFE056FD);
const Color _line3 = Color(0xFF8C7CF0);

const double _axisW = 64;
const double _timeH = 20;

/* ================================================================
   Chart widget
   ================================================================ */

/// Binance style candlestick chart.
///
/// Drag left or right to move through history, pinch (or mouse wheel) to
/// zoom, long press (or mouse hover) for the crosshair, double tap to jump
/// back to the newest candle.
class CandleChart extends StatefulWidget {
  final MarketController controller;
  final MainIndicator main;
  final SubIndicator sub;
  final double height;
  final Color upColor;
  final Color downColor;
  final String watermark;

  const CandleChart({
    super.key,
    required this.controller,
    this.main = MainIndicator.ma,
    this.sub = SubIndicator.vol,
    this.height = 380,
    this.upColor = kUpColor,
    this.downColor = kDownColor,
    this.watermark = 'BitXnow',
  });

  @override
  State<CandleChart> createState() => _CandleChartState();
}

class _CandleChartState extends State<CandleChart> {
  int _visible = 60;
  int _scrollBack = -6;
  int _scaleStartVisible = 60;
  double _dragCarry = 0;
  double _width = 300;
  int? _crossSlot;
  double? _crossY;
  String _dataKey = '';
  int _lastTime = 0;

  int _firstIndex(int length) => length - 1 - _scrollBack - (_visible - 1);

  double get _slot => math.max(1.0, _width - _axisW) / _visible;

  /// Resets the view when the coin or interval changes, and keeps the view
  /// still when a new candle arrives while the user looks at older candles.
  void _syncWithData() {
    final c = widget.controller;
    final key = '${c.symbol}|${c.interval}';
    if (key != _dataKey) {
      _dataKey = key;
      _scrollBack = -6;
      _crossSlot = null;
      _crossY = null;
      _lastTime = 0;
    }
    final candles = c.candles;
    final lastTime = candles.isEmpty ? 0 : candles.last.time;
    if (_lastTime != 0 && lastTime > _lastTime && _scrollBack > 0) {
      _scrollBack++;
    }
    _lastTime = lastTime;
  }

  void _onScaleStart(ScaleStartDetails d) {
    _scaleStartVisible = _visible;
    _dragCarry = 0;
  }

  void _onScaleUpdate(ScaleUpdateDetails d) {
    // Two fingers: pinch to zoom.
    if (d.pointerCount >= 2) {
      if (d.scale <= 0) return;
      final next = (_scaleStartVisible / d.scale).round().clamp(20, 240).toInt();
      if (next != _visible) setState(() => _visible = next);
      return;
    }

    // One finger: drag to move through history.
    _dragCarry += d.focalPointDelta.dx;
    final steps = (_dragCarry / _slot).truncate();
    if (steps == 0) return;
    _dragCarry -= steps * _slot;

    final maxBack = math.max(0, widget.controller.candles.length - 10);
    setState(() {
      _scrollBack = (_scrollBack + steps).clamp(-20, maxBack).toInt();
    });
  }

  void _onPointerSignal(PointerSignalEvent event) {
    if (event is! PointerScrollEvent) return;
    // Claim the wheel so the page does not scroll while zooming the chart.
    GestureBinding.instance.pointerSignalResolver.register(event, (e) {
      final scroll = e as PointerScrollEvent;
      setState(() {
        _visible = (_visible + (scroll.scrollDelta.dy > 0 ? 6 : -6))
            .clamp(20, 240)
            .toInt();
      });
    });
  }

  void _setCross(Offset p) {
    if (p.dx > _width - _axisW) {
      _clearCross();
      return;
    }
    setState(() {
      _crossSlot = (p.dx / _slot).floor().clamp(0, _visible - 1).toInt();
      _crossY = p.dy;
    });
  }

  void _clearCross() {
    if (_crossSlot == null && _crossY == null) return;
    setState(() {
      _crossSlot = null;
      _crossY = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: widget.controller,
      builder: (context, _) {
        _syncWithData();
        final c = widget.controller;

        return LayoutBuilder(
          builder: (context, constraints) {
            _width = constraints.maxWidth;

            return SizedBox(
              height: widget.height,
              child: Stack(
                children: [
                  Positioned.fill(
                    child: Listener(
                      onPointerSignal: _onPointerSignal,
                      child: MouseRegion(
                        onHover: (e) => _setCross(e.localPosition),
                        onExit: (_) => _clearCross(),
                        child: GestureDetector(
                          behavior: HitTestBehavior.opaque,
                          onScaleStart: _onScaleStart,
                          onScaleUpdate: _onScaleUpdate,
                          onLongPressStart: (d) => _setCross(d.localPosition),
                          onLongPressMoveUpdate: (d) =>
                              _setCross(d.localPosition),
                          onLongPressEnd: (_) => _clearCross(),
                          onTap: _clearCross,
                          onDoubleTap: () => setState(() => _scrollBack = -6),
                          child: RepaintBoundary(
                            child: CustomPaint(
                              painter: _ChartPainter(
                                candles: c.candles,
                                firstIndex: _firstIndex(c.candles.length),
                                visible: _visible,
                                decimals: c.decimals,
                                interval: c.interval,
                                main: widget.main,
                                sub: widget.sub,
                                up: widget.upColor,
                                down: widget.downColor,
                                crossSlot: _crossSlot,
                                crossY: _crossY,
                                watermark: widget.watermark,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  if (c.loading)
                    const Center(
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 14,
                            height: 14,
                            child: CircularProgressIndicator(
                              strokeWidth: 1.6,
                              color: _muted,
                            ),
                          ),
                          SizedBox(width: 8),
                          Text(
                            'Loading chart…',
                            style: TextStyle(color: _muted, fontSize: 11),
                          ),
                        ],
                      ),
                    ),
                  if (_scrollBack > 0)
                    Positioned(
                      right: _axisW + 8,
                      bottom: _timeH + 8,
                      child: GestureDetector(
                        onTap: () => setState(() => _scrollBack = -6),
                        child: Container(
                          padding: const EdgeInsets.all(5),
                          decoration: BoxDecoration(
                            color: _labelBg,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Icon(
                            Icons.keyboard_double_arrow_right_rounded,
                            color: _text,
                            size: 16,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}

/* ================================================================
   Indicator maths
   ================================================================ */

class _Line {
  final String label;
  final Color color;
  final List<double?> values;
  const _Line(this.label, this.color, this.values);
}

List<double?> _sma(List<double> v, int p) {
  final out = List<double?>.filled(v.length, null);
  var sum = 0.0;
  for (var i = 0; i < v.length; i++) {
    sum += v[i];
    if (i >= p) sum -= v[i - p];
    if (i >= p - 1) out[i] = sum / p;
  }
  return out;
}

List<double?> _ema(List<double?> v, int p) {
  final out = List<double?>.filled(v.length, null);
  final k = 2 / (p + 1);
  double? prev;
  var seedSum = 0.0;
  var seedCount = 0;
  for (var i = 0; i < v.length; i++) {
    final x = v[i];
    if (x == null) continue;
    if (prev == null) {
      seedSum += x;
      seedCount++;
      if (seedCount == p) {
        prev = seedSum / p;
        out[i] = prev;
      }
    } else {
      prev = x * k + prev * (1 - k);
      out[i] = prev;
    }
  }
  return out;
}

List<List<double?>> _boll(List<double> c, int p, double k) {
  final mid = _sma(c, p);
  final up = List<double?>.filled(c.length, null);
  final dn = List<double?>.filled(c.length, null);
  for (var i = p - 1; i < c.length; i++) {
    final m = mid[i];
    if (m == null) continue;
    var s = 0.0;
    for (var j = i - p + 1; j <= i; j++) {
      s += (c[j] - m) * (c[j] - m);
    }
    final sd = math.sqrt(s / p);
    up[i] = m + k * sd;
    dn[i] = m - k * sd;
  }
  return [up, mid, dn];
}

List<List<double?>> _macd(List<double> c) {
  final values = List<double?>.from(c);
  final e12 = _ema(values, 12);
  final e26 = _ema(values, 26);
  final dif = List<double?>.filled(c.length, null);
  for (var i = 0; i < c.length; i++) {
    final a = e12[i];
    final b = e26[i];
    if (a != null && b != null) dif[i] = a - b;
  }
  final dea = _ema(dif, 9);
  final hist = List<double?>.filled(c.length, null);
  for (var i = 0; i < c.length; i++) {
    final a = dif[i];
    final b = dea[i];
    if (a != null && b != null) hist[i] = (a - b) * 2;
  }
  return [dif, dea, hist];
}

List<double?> _rsi(List<double> c, int p) {
  final out = List<double?>.filled(c.length, null);
  var gain = 0.0;
  var loss = 0.0;
  for (var i = 1; i < c.length; i++) {
    final ch = c[i] - c[i - 1];
    final g = ch > 0 ? ch : 0.0;
    final l = ch < 0 ? -ch : 0.0;
    if (i <= p) {
      gain += g;
      loss += l;
      if (i == p) {
        gain /= p;
        loss /= p;
        out[i] = loss == 0 ? 100 : 100 - 100 / (1 + gain / loss);
      }
    } else {
      gain = (gain * (p - 1) + g) / p;
      loss = (loss * (p - 1) + l) / p;
      out[i] = loss == 0 ? 100 : 100 - 100 / (1 + gain / loss);
    }
  }
  return out;
}

List<List<double?>> _kdj(List<Candle> c, int p) {
  final kOut = List<double?>.filled(c.length, null);
  final dOut = List<double?>.filled(c.length, null);
  final jOut = List<double?>.filled(c.length, null);
  var k = 50.0;
  var d = 50.0;
  for (var i = p - 1; i < c.length; i++) {
    var hh = c[i].high;
    var ll = c[i].low;
    for (var j = i - p + 1; j < i; j++) {
      hh = math.max(hh, c[j].high);
      ll = math.min(ll, c[j].low);
    }
    final rsv = hh == ll ? 50.0 : (c[i].close - ll) / (hh - ll) * 100;
    k = k * 2 / 3 + rsv / 3;
    d = d * 2 / 3 + k / 3;
    kOut[i] = k;
    dOut[i] = d;
    jOut[i] = 3 * k - 2 * d;
  }
  return [kOut, dOut, jOut];
}

/* ================================================================
   Painter
   ================================================================ */

class _ChartPainter extends CustomPainter {
  final List<Candle> candles;
  final int firstIndex;
  final int visible;
  final int decimals;
  final String interval;
  final MainIndicator main;
  final SubIndicator sub;
  final Color up;
  final Color down;
  final int? crossSlot;
  final double? crossY;
  final String watermark;

  _ChartPainter({
    required this.candles,
    required this.firstIndex,
    required this.visible,
    required this.decimals,
    required this.interval,
    required this.main,
    required this.sub,
    required this.up,
    required this.down,
    required this.crossSlot,
    required this.crossY,
    required this.watermark,
  });

  /* ---------- small drawing helpers ---------- */

  TextPainter _tp(String s, Color color, double size,
      {FontWeight weight = FontWeight.w500, double? maxWidth}) {
    final tp = TextPainter(
      text: TextSpan(
        text: s,
        style: TextStyle(color: color, fontSize: size, fontWeight: weight),
      ),
      textDirection: TextDirection.ltr,
      maxLines: 1,
      ellipsis: '…',
    );
    tp.layout(maxWidth: maxWidth ?? double.infinity);
    return tp;
  }

  /// align: 0 = left, 0.5 = center, 1 = right
  void _txt(Canvas canvas, String s, Offset at, Color color, double size,
      {double align = 0, FontWeight weight = FontWeight.w500}) {
    final tp = _tp(s, color, size, weight: weight);
    tp.paint(canvas, Offset(at.dx - tp.width * align, at.dy));
  }

  void _label(Canvas canvas, String s, Rect rect, Color bg,
      {Color fg = Colors.white}) {
    canvas.drawRRect(
      RRect.fromRectAndRadius(rect, const Radius.circular(3)),
      Paint()..color = bg,
    );
    final tp = _tp(s, fg, 9.5, weight: FontWeight.w700, maxWidth: rect.width);
    tp.paint(
      canvas,
      Offset(
        rect.left + (rect.width - tp.width) / 2,
        rect.top + (rect.height - tp.height) / 2,
      ),
    );
  }

  void _dashH(Canvas canvas, double x1, double x2, double y, Paint p) {
    for (var x = x1; x < x2; x += 6) {
      canvas.drawLine(Offset(x, y), Offset(math.min(x + 3, x2), y), p);
    }
  }

  void _dashV(Canvas canvas, double x, double y1, double y2, Paint p) {
    for (var y = y1; y < y2; y += 6) {
      canvas.drawLine(Offset(x, y), Offset(x, math.min(y + 3, y2)), p);
    }
  }

  void _legend(Canvas canvas, List<InlineSpan> spans, Offset at, double maxW) {
    final tp = TextPainter(
      text: TextSpan(
        style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w600),
        children: spans,
      ),
      textDirection: TextDirection.ltr,
      maxLines: 1,
      ellipsis: '…',
    )..layout(maxWidth: maxW);
    tp.paint(canvas, at);
  }

  void _drawLines(
    Canvas canvas,
    List<_Line> lines,
    double slot,
    double Function(double) y,
    Rect clip,
  ) {
    canvas.save();
    canvas.clipRect(clip);
    for (final line in lines) {
      final path = Path();
      var started = false;
      for (var k = 0; k < visible; k++) {
        final i = firstIndex + k;
        if (i < 0 || i >= line.values.length) continue;
        final v = line.values[i];
        if (v == null) continue;
        final x = k * slot + slot / 2;
        if (started) {
          path.lineTo(x, y(v));
        } else {
          path.moveTo(x, y(v));
          started = true;
        }
      }
      canvas.drawPath(
        path,
        Paint()
          ..color = line.color
          ..style = PaintingStyle.stroke
          ..strokeWidth = 1,
      );
    }
    canvas.restore();
  }

  String _fmtSub(double? v) {
    if (v == null) return '--';
    switch (sub) {
      case SubIndicator.vol:
        return fmtCompact(v);
      case SubIndicator.macd:
        return fmtNum(v, decimals);
      default:
        return v.toStringAsFixed(2);
    }
  }

  /* ---------- paint ---------- */

  @override
  void paint(Canvas canvas, Size size) {
    final plotW = size.width - _axisW;
    final plotH = size.height - _timeH;
    if (plotW <= 20 || plotH <= 60) return;

    final n = candles.length;
    final slot = plotW / visible;
    final hasSub = sub != SubIndicator.none;
    final subH = hasSub ? plotH * 0.26 : 0.0;

    final mainRect = Rect.fromLTRB(0, 0, plotW, plotH - subH);
    final subRect = Rect.fromLTRB(0, plotH - subH, plotW, plotH);
    final pTop = 22.0;
    final pBot = mainRect.bottom - 10;
    final sTop = subRect.top + 16;
    final sBot = subRect.bottom - 2;

    final grid = Paint()
      ..color = _border.withValues(alpha: 0.45)
      ..strokeWidth = 0.6;
    final axis = Paint()
      ..color = _border
      ..strokeWidth = 1;

    // Watermark
    final wm = _tp(watermark, Colors.white.withValues(alpha: 0.05), 26,
        weight: FontWeight.w900);
    wm.paint(
      canvas,
      Offset(
        mainRect.center.dx - wm.width / 2,
        mainRect.center.dy - wm.height / 2,
      ),
    );

    // Frame
    canvas.drawLine(Offset(plotW, 0), Offset(plotW, plotH), axis);
    canvas.drawLine(Offset(0, plotH), Offset(size.width, plotH), axis);
    if (hasSub) {
      canvas.drawLine(
          Offset(0, subRect.top), Offset(size.width, subRect.top), axis);
    }

    // Vertical grid and time labels
    final step = math.max(1, (visible / 4).round());
    for (var k = step ~/ 2; k < visible; k += step) {
      final x = k * slot + slot / 2;
      canvas.drawLine(Offset(x, 0), Offset(x, plotH), grid);
      final i = firstIndex + k;
      if (i >= 0 && i < n) {
        _txt(canvas, axisTime(candles[i].time, interval), Offset(x, plotH + 4),
            _muted, 9,
            align: 0.5);
      }
    }

    /* ---------- indicator series ---------- */

    final closes = [for (final c in candles) c.close];

    List<_Line> mainLines = const [];
    String mainTitle = '';
    switch (main) {
      case MainIndicator.ma:
        mainLines = [
          _Line('MA(7)', _line1, _sma(closes, 7)),
          _Line('MA(25)', _line2, _sma(closes, 25)),
          _Line('MA(99)', _line3, _sma(closes, 99)),
        ];
        break;
      case MainIndicator.ema:
        final values = List<double?>.from(closes);
        mainLines = [
          _Line('EMA(7)', _line1, _ema(values, 7)),
          _Line('EMA(25)', _line2, _ema(values, 25)),
          _Line('EMA(99)', _line3, _ema(values, 99)),
        ];
        break;
      case MainIndicator.boll:
        final b = _boll(closes, 20, 2);
        mainTitle = 'BOLL(20,2)';
        mainLines = [
          _Line('UP', _line2, b[0]),
          _Line('MB', _line1, b[1]),
          _Line('DN', _line3, b[2]),
        ];
        break;
      case MainIndicator.none:
        break;
    }

    // Price range of the visible area.
    var hi = -double.infinity;
    var lo = double.infinity;
    var hiIdx = -1;
    var loIdx = -1;
    for (var k = 0; k < visible; k++) {
      final i = firstIndex + k;
      if (i < 0 || i >= n) continue;
      final c = candles[i];
      if (c.high > hi) {
        hi = c.high;
        hiIdx = i;
      }
      if (c.low < lo) {
        lo = c.low;
        loIdx = i;
      }
    }

    if (hiIdx < 0) {
      for (var g = 0; g <= 4; g++) {
        final yy = pTop + (pBot - pTop) * g / 4;
        canvas.drawLine(Offset(0, yy), Offset(plotW, yy), grid);
      }
      return;
    }

    final candleHi = hi;
    final candleLo = lo;
    for (final line in mainLines) {
      for (var k = 0; k < visible; k++) {
        final i = firstIndex + k;
        if (i < 0 || i >= n) continue;
        final v = line.values[i];
        if (v == null) continue;
        hi = math.max(hi, v);
        lo = math.min(lo, v);
      }
    }
    if (hi == lo) {
      final bump = hi.abs() * 0.001 + 1e-8;
      hi += bump;
      lo -= bump;
    }
    final pad = (hi - lo) * 0.04;
    hi += pad;
    lo -= pad;

    double y(double p) => pTop + (hi - p) / (hi - lo) * (pBot - pTop);
    double priceAt(double yy) => hi - (yy - pTop) / (pBot - pTop) * (hi - lo);

    // Horizontal grid with price labels
    for (var g = 0; g <= 4; g++) {
      final yy = pTop + (pBot - pTop) * g / 4;
      canvas.drawLine(Offset(0, yy), Offset(plotW, yy), grid);
      _txt(canvas, fmtNum(priceAt(yy), decimals), Offset(plotW + 4, yy - 6),
          _muted, 9);
    }

    // Candles
    final bodyW = math.max(1.0, slot * 0.7);
    final wick = Paint()..strokeWidth = 1;
    final fill = Paint();
    for (var k = 0; k < visible; k++) {
      final i = firstIndex + k;
      if (i < 0 || i >= n) continue;
      final c = candles[i];
      final color = c.isUp ? up : down;
      final x = k * slot + slot / 2;

      wick.color = color;
      canvas.drawLine(Offset(x, y(c.high)), Offset(x, y(c.low)), wick);

      final top = y(math.max(c.open, c.close));
      final bottom = y(math.min(c.open, c.close));
      fill.color = color;
      canvas.drawRect(
        Rect.fromLTRB(
            x - bodyW / 2, top, x + bodyW / 2, math.max(bottom, top + 1)),
        fill,
      );
    }

    // Main indicator lines
    _drawLines(canvas, mainLines, slot, y,
        Rect.fromLTRB(0, pTop - 4, plotW, mainRect.bottom));

    // Highest and lowest price markers
    void marker(int idx, double price, bool isHigh) {
      final k = idx - firstIndex;
      final x = k * slot + slot / 2;
      final yy = y(price);
      final p = Paint()
        ..color = _text.withValues(alpha: 0.7)
        ..strokeWidth = 0.8;
      final text = fmtNum(price, decimals);
      final ty = isHigh ? yy - 6 : yy - 5;
      if (x < plotW / 2) {
        canvas.drawLine(Offset(x, yy), Offset(x + 14, yy), p);
        _txt(canvas, text, Offset(x + 16, ty), _text, 9);
      } else {
        canvas.drawLine(Offset(x - 14, yy), Offset(x, yy), p);
        _txt(canvas, text, Offset(x - 16, ty), _text, 9, align: 1);
      }
    }

    marker(hiIdx, candleHi, true);
    marker(loIdx, candleLo, false);

    // Last price line and label
    final last = candles.last;
    final lastColor = last.isUp ? up : down;
    final ly = y(last.close).clamp(pTop, pBot).toDouble();
    _dashH(
      canvas,
      0,
      plotW,
      ly,
      Paint()
        ..color = lastColor.withValues(alpha: 0.75)
        ..strokeWidth = 0.8,
    );
    _label(canvas, fmtNum(last.close, decimals),
        Rect.fromLTWH(plotW + 1, ly - 8, _axisW - 2, 16), lastColor);

    // Index used by the legends: crosshair candle or newest candle.
    var li = n - 1;
    if (crossSlot != null) {
      final ci = firstIndex + crossSlot!;
      if (ci >= 0 && ci < n) li = ci;
    }

    // Main legend
    if (mainLines.isNotEmpty) {
      _legend(
        canvas,
        [
          if (mainTitle.isNotEmpty)
            TextSpan(
              text: '$mainTitle  ',
              style: const TextStyle(color: _muted),
            ),
          for (final line in mainLines)
            TextSpan(
              text: '${line.label}: ${fmtNum(line.values[li], decimals)}   ',
              style: TextStyle(color: line.color),
            ),
        ],
        const Offset(4, 4),
        plotW - 8,
      );
    }

    /* ---------- lower panel ---------- */

    double subMin = 0;
    double subMax = 1;
    double Function(double) sy = (v) => sBot;

    if (hasSub) {
      List<_Line> subLines = const [];
      List<double?>? bars;
      var barsBySign = false;
      String subTitle = '';
      double? fixedMin;
      double? fixedMax;

      switch (sub) {
        case SubIndicator.vol:
          final vols = [for (final c in candles) c.volume];
          bars = List<double?>.from(vols);
          subTitle = 'VOL: ${fmtCompact(vols[li])}';
          subLines = [
            _Line('MA(5)', _line1, _sma(vols, 5)),
            _Line('MA(10)', _line2, _sma(vols, 10)),
          ];
          fixedMin = 0;
          break;
        case SubIndicator.macd:
          final m = _macd(closes);
          bars = m[2];
          barsBySign = true;
          subTitle = 'MACD(12,26,9)  MACD: ${_fmtSub(m[2][li])}';
          subLines = [
            _Line('DIF', _line1, m[0]),
            _Line('DEA', _line2, m[1]),
          ];
          break;
        case SubIndicator.rsi:
          subLines = [
            _Line('RSI(6)', _line1, _rsi(closes, 6)),
            _Line('RSI(12)', _line2, _rsi(closes, 12)),
            _Line('RSI(24)', _line3, _rsi(closes, 24)),
          ];
          fixedMin = 0;
          fixedMax = 100;
          break;
        case SubIndicator.kdj:
          final kd = _kdj(candles, 9);
          subTitle = 'KDJ(9,3,3)';
          subLines = [
            _Line('K', _line1, kd[0]),
            _Line('D', _line2, kd[1]),
            _Line('J', _line3, kd[2]),
          ];
          break;
        case SubIndicator.none:
          break;
      }

      // Range of the lower panel
      var mn = double.infinity;
      var mx = -double.infinity;
      void take(double? v) {
        if (v == null || !v.isFinite) return;
        mn = math.min(mn, v);
        mx = math.max(mx, v);
      }

      for (var k = 0; k < visible; k++) {
        final i = firstIndex + k;
        if (i < 0 || i >= n) continue;
        if (bars != null) take(bars[i]);
        for (final line in subLines) {
          take(line.values[i]);
        }
      }
      if (barsBySign) take(0);
      if (fixedMin != null) mn = fixedMin;
      if (fixedMax != null) mx = fixedMax;
      if (!mn.isFinite || !mx.isFinite) {
        mn = 0;
        mx = 1;
      }
      if (mx == mn) mx = mn + 1;
      subMin = mn;
      subMax = mx;
      sy = (v) => sTop + (subMax - v) / (subMax - subMin) * (sBot - sTop);

      // RSI guide lines
      if (sub == SubIndicator.rsi) {
        final guide = Paint()
          ..color = _muted.withValues(alpha: 0.35)
          ..strokeWidth = 0.7;
        _dashH(canvas, 0, plotW, sy(70), guide);
        _dashH(canvas, 0, plotW, sy(30), guide);
      }

      // Bars (volume or MACD histogram)
      if (bars != null) {
        final base = barsBySign ? sy(0) : sBot;
        if (barsBySign) {
          canvas.drawLine(
            Offset(0, base),
            Offset(plotW, base),
            Paint()
              ..color = _border
              ..strokeWidth = 0.6,
          );
        }
        for (var k = 0; k < visible; k++) {
          final i = firstIndex + k;
          if (i < 0 || i >= n) continue;
          final v = bars[i];
          if (v == null) continue;
          final x = k * slot + slot / 2;
          final positive = barsBySign ? v >= 0 : candles[i].isUp;
          fill.color = (positive ? up : down)
              .withValues(alpha: barsBySign ? 0.8 : 0.55);
          final top = math.min(sy(v), base);
          final bottom = math.max(sy(v), base);
          canvas.drawRect(
            Rect.fromLTRB(x - bodyW / 2, top, x + bodyW / 2,
                math.max(bottom, top + 0.8)),
            fill,
          );
        }
      }

      _drawLines(canvas, subLines, slot, sy,
          Rect.fromLTRB(0, subRect.top + 1, plotW, subRect.bottom));

      // Lower panel legend and axis value
      _legend(
        canvas,
        [
          if (subTitle.isNotEmpty)
            TextSpan(
              text: '$subTitle   ',
              style: const TextStyle(color: _muted),
            ),
          for (final line in subLines)
            TextSpan(
              text: '${line.label}: ${_fmtSub(line.values[li])}   ',
              style: TextStyle(color: line.color),
            ),
        ],
        Offset(4, subRect.top + 3),
        plotW - 8,
      );
      _txt(canvas, _fmtSub(subMax), Offset(plotW + 4, sTop - 5), _muted, 9);
    }

    /* ---------- crosshair ---------- */

    if (crossSlot != null) {
      final cross = Paint()
        ..color = _muted
        ..strokeWidth = 0.8;
      final x = crossSlot! * slot + slot / 2;
      _dashV(canvas, x, 0, plotH, cross);

      final i = firstIndex + crossSlot!;
      final valid = i >= 0 && i < n;

      if (valid) {
        final label = fullTime(candles[i].time, interval);
        final w = label.length * 5.6 + 12;
        final left =
            (x - w / 2).clamp(0.0, math.max(0.0, size.width - w)).toDouble();
        _label(canvas, label, Rect.fromLTWH(left, plotH + 2, w, 16), _labelBg);
      }

      final cy = crossY;
      if (cy != null && cy >= pTop && cy <= pBot) {
        _dashH(canvas, 0, plotW, cy, cross);
        _label(canvas, fmtNum(priceAt(cy), decimals),
            Rect.fromLTWH(plotW + 1, cy - 8, _axisW - 2, 16), _labelBg);
      } else if (hasSub && cy != null && cy >= sTop && cy <= sBot) {
        _dashH(canvas, 0, plotW, cy, cross);
        final v = subMax - (cy - sTop) / (sBot - sTop) * (subMax - subMin);
        _label(canvas, _fmtSub(v),
            Rect.fromLTWH(plotW + 1, cy - 8, _axisW - 2, 16), _labelBg);
      }

      if (valid) _infoBox(canvas, candles[i], x, plotW, pTop);
    }
  }

  /// Floating box with the candle details, like Binance.
  void _infoBox(Canvas canvas, Candle c, double x, double plotW, double top) {
    final change = c.close - c.open;
    final pct = c.open == 0 ? 0.0 : change / c.open * 100;
    final amp = c.open == 0 ? 0.0 : (c.high - c.low) / c.open * 100;
    final changeColor = change >= 0 ? up : down;

    final rows = <List<Object>>[
      ['Time', fullTime(c.time, interval), _text],
      ['Open', fmtNum(c.open, decimals), _text],
      ['High', fmtNum(c.high, decimals), _text],
      ['Low', fmtNum(c.low, decimals), _text],
      ['Close', fmtNum(c.close, decimals), _text],
      ['Change', fmtNum(change, decimals), changeColor],
      ['Change%', fmtPct(pct), changeColor],
      ['Amplitude', '${amp.toStringAsFixed(2)}%', _text],
      ['Volume', fmtCompact(c.volume), _text],
    ];

    const w = 152.0;
    const rowH = 14.0;
    final h = rows.length * rowH + 10;
    final left = x > plotW / 2 ? 6.0 : plotW - w - 6;
    final rect = Rect.fromLTWH(left, top, w, h);

    canvas.drawRRect(
      RRect.fromRectAndRadius(rect, const Radius.circular(6)),
      Paint()..color = _boxBg,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(rect, const Radius.circular(6)),
      Paint()
        ..color = _border
        ..style = PaintingStyle.stroke
        ..strokeWidth = 0.8,
    );

    for (var r = 0; r < rows.length; r++) {
      final yy = top + 5 + r * rowH;
      _txt(canvas, rows[r][0] as String, Offset(left + 8, yy), _muted, 9.5);
      _txt(canvas, rows[r][1] as String, Offset(left + w - 8, yy),
          rows[r][2] as Color, 9.5,
          align: 1, weight: FontWeight.w600);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}