<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceClient;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request, AuthServiceClient $auth)
    {
        $response = $auth->login($request->only('email', 'password'));
        if (!($response['success'] ?? false)) return back()->withErrors(['email' => $response['message'] ?? 'Login failed']);

        $user = $response['data']['user'];
        session(['token' => $response['data']['token'], 'user' => $user]);

        if (($user['role'] ?? '') === 'admin') {
            return redirect()->route('dashboard.admin');
        }
        return redirect()->route('events.index');
    }

    public function register(Request $request, AuthServiceClient $auth)
    {
        $payload = $request->only('name', 'email', 'password') + ['password_confirmation' => $request->input('password_confirmation')];
        $response = $auth->register($payload);
        if (!($response['success'] ?? false)) return back()->withErrors(['email' => $response['message'] ?? 'Register failed']);

        $user = $response['data']['user'];
        session(['token' => $response['data']['token'], 'user' => $user]);

        if (($user['role'] ?? '') === 'admin') {
            return redirect()->route('dashboard.admin');
        }
        return redirect()->route('events.index');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
