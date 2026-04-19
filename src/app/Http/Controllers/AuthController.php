<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('general.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'general',
        ]);

        auth()->login($user);
        return redirect()->route('verification.notice');
    }

    public function showLogin()
    {
        return view('general.login');
    }

    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            if (!auth()->user()->isGeneral()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ]);
            }

            return redirect()->route('attendance_index');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}