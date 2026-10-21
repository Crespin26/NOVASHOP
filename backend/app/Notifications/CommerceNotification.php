<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommerceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $subject, public string $message, public ?string $url = null)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->line($this->message);
        if ($this->url) {
            $mail->action($this->locale === 'en' ? 'Continue' : 'Continuer', $this->url);
        }

        return $mail;
    }
}
