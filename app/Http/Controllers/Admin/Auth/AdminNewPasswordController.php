<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Models\Admin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', ['email' => $request->email, 'token' => $token]);
    }

    public function store(AdminResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('admins')->reset($request->only('email', 'password', 'password_confirmation', 'token'), function (Admin $admin) use ($request) {
            $admin->forceFill(['password' => Hash::make($request->string('password')), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($admin));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('status', 'Password admin berhasil diatur ulang. Silakan masuk.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'Tautan reset password tidak valid atau telah kedaluwarsa.']);
    }
}
