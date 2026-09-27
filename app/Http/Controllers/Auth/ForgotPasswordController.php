<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(): View
    {
        return view('adminlte::auth.passwords.email');
    }

    public function broker(): \Illuminate\Auth\Passwords\PasswordBroker
    {
        return Password::broker('admins');
    }
}
