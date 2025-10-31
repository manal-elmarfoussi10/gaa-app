<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountActivatedNotification extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre compte a été activé — GS Auto')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre compte administrateur pour ' . $notifiable->company->name . ' a été activé avec succès.')
            ->line('Vous pouvez maintenant accéder à votre tableau de bord et commencer à utiliser nos services.')
            ->action('Accéder à mon compte', url('/login'))
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
            ->salutation("Cordialement,\nGS Auto");
    }
}
