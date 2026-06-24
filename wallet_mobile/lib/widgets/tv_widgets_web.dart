// ignore: avoid_web_libraries_in_flutter
import 'dart:html' as html;
import 'dart:ui_web' as ui_web;
import 'package:flutter/material.dart';

bool _registered = false;
String _tickerHtml = '';
String _newsHtml = '';

void registerTvViews(String tickerHtml, String newsHtml) {
  if (_registered) return;
  _registered = true;
  _tickerHtml = tickerHtml;
  _newsHtml = newsHtml;

  ui_web.platformViewRegistry.registerViewFactory('tv-ticker', (int id) {
    final iframe = html.IFrameElement()
      ..srcdoc = _tickerHtml
      ..style.border = 'none'
      ..style.width = '100%'
      ..style.height = '100%';
    return iframe;
  });

  ui_web.platformViewRegistry.registerViewFactory('tv-news', (int id) {
    final iframe = html.IFrameElement()
      ..srcdoc = _newsHtml
      ..style.border = 'none'
      ..style.width = '100%'
      ..style.height = '100%';
    return iframe;
  });
}

class TvTickerView extends StatelessWidget {
  final String html;
  const TvTickerView({super.key, required this.html});
  @override
  Widget build(BuildContext context) {
    return const HtmlElementView(viewType: 'tv-ticker');
  }
}

class TvNewsView extends StatelessWidget {
  final String html;
  const TvNewsView({super.key, required this.html});
  @override
  Widget build(BuildContext context) {
    return const HtmlElementView(viewType: 'tv-news');
  }
}