<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'tickets' => SupportTicket::where('user_id', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket submitted successfully.',
            'ticket' => $ticket,
        ]);
    }
}