<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()
            ->withErrors([
                'email' => 'Email atau password tidak sesuai.',
            ])
            ->onlyInput('email');
    }

    // Menampilkan halaman register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Proses register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        // Role tidak diterima dari formulir.
        // Database otomatis memberikan role "user".
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/dashboard')
            ->with('success', 'Registrasi berhasil. Selamat datang di ThreatMetrics!');
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}
