<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create()
    {
        return view('auth.login', [
            'schools' => SchoolSetting::orderBy('school_name')->get(),
            'years' => SchoolYear::orderByDesc('starts_on')->get(),
            'activeYear' => SchoolYear::active(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_setting_id' => ['nullable', 'exists:school_settings,id'],
            'school_year_id' => ['nullable', 'exists:school_years,id'],
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $credentials = ['username' => $data['username'], 'password' => $data['password']];
        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true])) {
            return back()->withErrors(['username' => 'Identifiants incorrects.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        if (!empty($data['school_setting_id'])) {
            $request->session()->put('school_setting_id', (int) $data['school_setting_id']);
        }

        if (!empty($data['school_year_id'])) {
            $request->session()->put('school_year_id', (int) $data['school_year_id']);
        }

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
