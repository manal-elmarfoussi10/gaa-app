<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $url) {}

    public function via($notifiable) { return ['mail']; }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Réinitialisation du mot de passe — GS Auto')
            ->greeting('Bonjour,')
            ->line('Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.')
            ->action('Réinitialiser le mot de passe', $this->url)
            ->line("Ce lien expirera dans 60 minutes.")
            ->line("Si vous n'êtes pas à l'origine de cette demande, aucune action n'est requise.")
            ->salutation("Cordialement,\nGS Auto");
    }
}