<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    private function adminOnly(): void
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();

        $users = User::whereIn('role', ['client', 'merchant'])
            ->latest()
            ->paginate(20);

        return view('admin.settings.index', compact('users'));
    }

    public function toggleVerification(User $user)
    {
        $this->adminOnly();

        if ($user->role === 'admin') {
            return back()->withErrors('Admin verification cannot be changed.');
        }

        $user->update([
            'is_verified' => !$user->is_verified,
        ]);

        return back()->with('success', $user->is_verified ? 'User verified successfully.' : 'User marked as unverified.');
    }
}