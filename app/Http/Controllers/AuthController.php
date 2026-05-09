<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate(['username' => ['required'], 'password' => ['required']]);
        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true])) {
            return back()->withErrors(['username' => 'Identifiants incorrects.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
