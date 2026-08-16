<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\CustomerForgotPasswordRequest;
use App\Http\Requests\Customer\Auth\CustomerLoginRequest;
use App\Http\Requests\Customer\Auth\CustomerRegistrationRequest;
use App\Http\Requests\Customer\Auth\CustomerResetPasswordRequest;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function createRegistration(): View
    {
        return view('customer.auth.register');
    }

    public function register(CustomerRegistrationRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());
        Auth::guard('customer')->login($customer);
        $this->cartService->mergeGuestCartIntoCustomer($customer->id);
        $request->session()->regenerate();

        return redirect()->route('customer.profile.edit')->with('success', 'Akun berhasil dibuat. Selamat datang di Suki Craft!');
    }

    public function createLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.profile.edit');
        }

        return view('customer.auth.login');
    }

    public function login(CustomerLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $this->cartService->mergeGuestCartIntoCustomer($request->user('customer')->id);
        $request->session()->regenerate();
        $intendedUrl = $request->session()->pull('url.intended');

        return redirect()->to(Str::startsWith((string) $intendedUrl, url('/akun')) ? $intendedUrl : route('customer.profile.edit'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function createPasswordResetLink(): View
    {
        return view('customer.auth.forgot-password');
    }

    public function sendPasswordResetLink(CustomerForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Jika email terdaftar, tautan reset password telah dikirim.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'Kami belum dapat mengirim tautan reset password.']);
    }

    public function createNewPassword(Request $request, string $token): View
    {
        return view('customer.auth.reset-password', ['email' => $request->email, 'token' => $token]);
    }

    public function resetPassword(CustomerResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('customers')->reset($request->only('email', 'password', 'password_confirmation', 'token'), function (Customer $customer) use ($request) {
            $customer->forceFill(['password' => Hash::make($request->string('password')), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($customer));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('customer.login')->with('status', 'Password berhasil diatur ulang. Silakan masuk.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'Tautan reset password tidak valid atau telah kedaluwarsa.']);
    }
}
