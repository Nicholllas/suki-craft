<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class AdminResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        return (new MailMessage)
            ->subject('Reset Password Admin Suki Craft')
            ->line('Kami menerima permintaan reset password untuk akun admin Anda.')
            ->action('Reset Password Admin', $url)
            ->line(Lang::get('Tautan reset password ini akan kedaluwarsa dalam :count menit.', ['count' => config('auth.passwords.admins.expire')]))
            ->line('Jika Anda tidak meminta reset password, Anda tidak perlu melakukan tindakan apa pun.');
    }
}
