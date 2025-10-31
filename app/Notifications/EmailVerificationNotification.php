<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(public string $url) {}

    public function via($notifiable) { return ['mail']; }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Vérification de votre email — GS Auto')
            ->greeting('Bonjour,')
            ->line('Nous avons reçu une demande d\'inscription pour le compte administrateur de ' . $notifiable->company->name . '.')
            ->line('Pour finaliser votre inscription, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous.')
            ->action('Vérifier mon email', $this->url)
            ->line("Ce lien expirera dans 60 minutes.")
            ->line("Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email.")
            ->salutation("Cordialement,\nGS Auto");
    }
}
