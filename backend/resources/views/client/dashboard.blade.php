@extends('layouts.admin', ['title' => 'Client Dashboard'])

@section('content')
@include('shared.dark_dashboard', [
    'mode' => 'client',
    'user' => $user,
    'latestRequests' => $latestRequests,
    'latestTransfers' => $latestTransfers,
])
@endsection