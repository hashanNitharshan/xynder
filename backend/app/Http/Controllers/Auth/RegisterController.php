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
            'role' => ['required', Rule::in(['client', 'merchant'])],

            'name' => ['required', 'string', 'max:255'],
            'original_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],

            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_type' => ['nullable', 'string', 'max:100'],
            'ifsc' => ['nullable', 'string', 'max:100'],
            'upi_name' => ['nullable', 'string', 'max:255'],
            'upi_id' => ['nullable', 'string', 'max:255'],

            'password' => ['required', 'string', 'min:6', 'confirmed'],

            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'aadhaar_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'upi_qr' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $photo = $request->hasFile('photo')
            ? $request->file('photo')->store('users/photos', 'public')
            : null;

        $aadhaarPhoto = $request->hasFile('aadhaar_photo')
            ? $request->file('aadhaar_photo')->store('users/aadhaar', 'public')
            : null;

        $upiQr = $request->hasFile('upi_qr')
            ? $request->file('upi_qr')->store('users/upi_qr', 'public')
            : null;

        $user = User::create([
            'name' => $data['name'],
            'original_name' => $data['original_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'balance' => 0,
            'is_active' => true,
            'status' => 'active',
            'is_verified' => false,
            'wallet_id' => $this->generateWalletId(),

            'country' => $data['country'] ?? null,
            'state' => $data['state'] ?? null,
            'address' => $data['address'] ?? null,

            'bank_name' => $data['bank_name'] ?? null,
            'branch' => $data['branch'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_type' => $data['account_type'] ?? null,
            'ifsc' => $data['ifsc'] ?? null,
            'upi_name' => $data['upi_name'] ?? null,
            'upi_id' => $data['upi_id'] ?? null,

            'photo' => $photo,
            'aadhaar_photo' => $aadhaarPhoto,
            'upi_qr' => $upiQr,
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