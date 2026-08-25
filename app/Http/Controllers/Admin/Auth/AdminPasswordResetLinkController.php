<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminPasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function store(AdminForgotPasswordRequest $request): RedirectResponse
    {
        try {
            $status = Password::broker('admins')->sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Kami belum dapat mengirim tautan reset password. Silakan coba lagi nanti.',
            ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Jika email terdaftar, tautan reset password telah dikirim.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'Kami belum dapat mengirim tautan reset password.']);
    }
}
