<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid email or password'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (($user->status ?? 'active') !== 'active' || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Your account has been blocked. Please contact admin.'])
                ->onlyInput('email');
        }

        // Respects the admin "Go Offline" option.
        Presence::touch($user, true);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    { 
        Presence::offline($request->user());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
