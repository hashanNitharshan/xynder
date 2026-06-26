<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
 
        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => 'client',
            'balance'     => 0,
            'is_active'   => true,
            'status'      => 'active',
            'is_verified' => false,
            'wallet_id'   => $this->generateWalletId(),
        ]);
 
        Auth::login($user);
 
        return redirect()->route('dashboard');
    }

    private function generateWalletId(): string
    {
        do {
            $id = 'XYN' . random_int(10000000, 99999999);
        } while (User::where('wallet_id', $id)->exists());

        return $id;
    }
}