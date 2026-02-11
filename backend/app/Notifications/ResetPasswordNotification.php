<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('パスワードリセットのご案内')
            ->greeting('パスワードリセットのリクエストを受け付けました')
            ->line('下のボタンをクリックして、新しいパスワードを設定してください。')
            ->action('パスワードをリセット', $url)
            ->line("このリンクは {$expire} 分で期限切れになります。")
            ->line('パスワードリセットを要求していない場合は、このメールを無視してください。');
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url', config('app.url', 'http://localhost:3000'));
        $frontendUrl = rtrim($frontendUrl, '/');

        return $frontendUrl.'/reset-password?'
            .http_build_query([
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
    }
}
