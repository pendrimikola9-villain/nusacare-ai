<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return Auth::user()->role === 'medis'
                ? redirect()->route('medis.dashboard')
                : redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'health_category' => 'required|string|max:100',
            'kelurahan' => 'required|string|max:100',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'warga',
            'health_category' => $data['health_category'],
            'kelurahan' => $data['kelurahan'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun NusaCare AI berhasil dibuat!');
    }

    public function demoLogin(Request $request, string $role)
    {
        $email = $role === 'medis' ? 'medis@nusacare.id' : 'warga@nusacare.id';
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Akun demo belum tersedia. Jalankan php artisan db:seed terlebih dahulu.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $role === 'medis'
            ? redirect()->route('medis.dashboard')
            : redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}