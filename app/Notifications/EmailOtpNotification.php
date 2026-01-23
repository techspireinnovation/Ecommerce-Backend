<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailOtpNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('Your OTP Code')
        ->markdown('emails.otp', ['code' => $this->code]);
}

}
