import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

const String _tickerHtml = '''
<!DOCTYPE html><html>
<head><meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>html,body{margin:0;padding:0;background:#181a20;overflow:hidden;}</style>
</head>
<body>
<script type="module" src="https://widgets.tradingview-widget.com/w/en/tv-ticker-tape.js"></script>
<tv-ticker-tape symbols="FOREXCOM:SPXUSD,FOREXCOM:NSXUSD,FOREXCOM:DJI,FX:EURUSD,BITSTAMP:BTCUSD,BITSTAMP:ETHUSD,CMCMARKETS:GOLD"></tv-ticker-tape>
</body></html>
''';

const String _newsHtml = '''
<!DOCTYPE html><html>
<head><meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>html,body{margin:0;padding:0;background:#181a20;overflow:hidden;}</style>
</head>
<body>
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-timeline.js" async>
  {"displayMode":"regular","feedMode":"all_symbols","colorTheme":"dark","isTransparent":false,"locale":"en","width":"100%","height":550}
  </script>
</div>
</body></html>
''';

class TvTickerWidget extends StatefulWidget {
  const TvTickerWidget({super.key});
  @override
  State<TvTickerWidget> createState() => _TvTickerWidgetState();
}

class _TvTickerWidgetState extends State<TvTickerWidget> {
  WebViewController? _controller;

  @override
  void initState() {
    super.initState();
    if (!kIsWeb) {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(const Color(0xff181a20))
        ..loadHtmlString(_tickerHtml);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (kIsWeb) {
      return Container(
        height: 82,
        decoration: BoxDecoration(
          color: const Color(0xff181a20),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xff2b3139)),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: _WebTicker(),
        ),
      );
    }
    if (_controller == null) return const SizedBox(height: 82);
    return Container(
      height: 82,
      decoration: BoxDecoration(
        color: const Color(0xff181a20),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xff2b3139)),
      ),
      clipBehavior: Clip.antiAlias,
      child: WebViewWidget(controller: _controller!),
    );
  }
}

class TvNewsWidget extends StatefulWidget {
  const TvNewsWidget({super.key});
  @override
  State<TvNewsWidget> createState() => _TvNewsWidgetState();
}

class _TvNewsWidgetState extends State<TvNewsWidget> {
  WebViewController? _controller;

  @override
  void initState() {
    super.initState();
    if (!kIsWeb) {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(const Color(0xff181a20))
        ..loadHtmlString(_newsHtml);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (kIsWeb) {
      return Container(
        height: 550,
        decoration: BoxDecoration(
          color: const Color(0xff181a20),
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: const Color(0xff2b3139)),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(22),
          child: _WebNews(),
        ),
      );
    }
    if (_controller == null) return const SizedBox(height: 550);
    return Container(
      height: 550,
      decoration: BoxDecoration(
        color: const Color(0xff181a20),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xff2b3139)),
      ),
      clipBehavior: Clip.antiAlias,
      child: WebViewWidget(controller: _controller!),
    );
  }
}