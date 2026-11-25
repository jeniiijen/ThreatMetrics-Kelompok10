<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('account.settings', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $request->user()->update(['password' => $validated['password']]);
        $request->session()->regenerate();

        return redirect()->route('account.settings')
            ->with('success', 'Kata sandi berhasil diubah.');
    }
}
