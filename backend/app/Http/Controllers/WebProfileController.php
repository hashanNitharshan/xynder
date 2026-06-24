<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebProfileController extends Controller
{
    public function clientIndex(Request $request)
    {
        abort_unless($request->user()->role === 'client', 403);

        return $this->index($request, 'client.profile');
    }

    public function merchantIndex(Request $request)
    {
        abort_unless($request->user()->role === 'merchant', 403);

        return $this->index($request, 'merchant.profile');
    }

    private function index(Request $request, string $view)
    {
        $user = $request->user()->fresh();

        return view($view, compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['client', 'merchant'], true), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'original_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',

            'bank_name' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'account_type' => 'nullable|string|max:100',
            'ifsc' => 'nullable|string|max:100',

            'upi_name' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',

            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'aadhaar_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'upi_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        foreach (['photo', 'aadhaar_photo', 'upi_qr'] as $field) {
            if ($request->hasFile($field)) {
                if ($user->{$field}) {
                    Storage::disk('public')->delete($user->{$field});
                }

                $folder = match ($field) {
                    'photo' => 'users/photos',
                    'aadhaar_photo' => 'users/aadhaar',
                    'upi_qr' => 'users/upi_qr',
                };

                $data[$field] = $request->file($field)->store($folder, 'public');
            }
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}