@extends('layouts.admin', ['title' => 'Client Dashboard'])
<title>BITXNOW</title>
<link rel="icon" type="image/jpeg" href="{{ asset('images/bitxnow_logo.jpeg') }}">
<link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/bitxnow_logo.jpeg') }}">
<link rel="apple-touch-icon" href="{{ asset('images/bitxnow_logo.jpeg') }}">
@section('content')
@include('shared.dark_dashboard', [
    'mode' => 'client',
    'user' => $user,
    'latestRequests' => $latestRequests,
    'latestTransfers' => $latestTransfers,
])
@endsection