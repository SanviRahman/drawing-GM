<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected string $redirectTo = '/admin';

    public function showResetForm(Request $request): View
    {
        return view('adminlte::auth.passwords.reset', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    public function broker(): \Illuminate\Auth\Passwords\PasswordBroker
    {
        return Password::broker('admins');
    }

    public function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard('admin');
    }
}
