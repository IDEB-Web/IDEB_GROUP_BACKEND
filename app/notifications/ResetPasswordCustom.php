<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCustom extends Notification
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Restablecer Contraseña')
            ->line('Haz clic en el siguiente enlace para restablecer tu contraseña:')
            ->action('Restablecer Contraseña', $this->url)
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }
}
