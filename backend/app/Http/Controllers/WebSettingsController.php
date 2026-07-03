<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebSettingsController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.settings');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.settings');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()->fresh();

        $tickets = SupportTicket::where('user_id', $user->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view($view, compact('user', 'tickets'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        return redirect()->route($user->role === 'merchant' ? 'merchant.profile' : 'client.profile');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function storeSupport(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        SupportTicket::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Support ticket submitted successfully.');
    }
}
