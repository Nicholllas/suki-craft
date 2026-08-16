<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CustomerResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('customer.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        return (new MailMessage)
            ->subject('Reset Password Akun Suki Craft')
            ->line('Kami menerima permintaan reset password untuk akun Suki Craft Anda.')
            ->action('Reset Password', $url)
            ->line(Lang::get('Tautan reset password ini akan kedaluwarsa dalam :count menit.', ['count' => config('auth.passwords.customers.expire')]))
            ->line('Jika Anda tidak meminta reset password, Anda tidak perlu melakukan tindakan apa pun.');
    }
}
