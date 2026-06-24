@extends('layouts.admin', ['title' => 'Merchant Dashboard'])

@section('content')
@include('shared.dark_dashboard', [
    'mode' => 'merchant',
    'user' => $user,
    'latestRequests' => $latestRequests,
    'latestTransfers' => $latestTransfers,
])
@endsection