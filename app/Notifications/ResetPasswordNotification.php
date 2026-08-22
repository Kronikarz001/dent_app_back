<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Summary of ResetPasswordNotification
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * @param string $token
     */
    public function __construct(
        private readonly string $token
    ) {}

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = config('app.frontend_url')
            .'reset-password?token='.$this->token.'&email='.$notifiable->email;

        return (new MailMessage)
            ->subject('Reset hasła')
            ->line('Kliknij poniższy link, aby zresetować hasło:')
            ->action('Resetuj hasło', $url)
            ->line('Jeśli to nie Ty, zignoruj tę wiadomość.');
    }

    /**
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
