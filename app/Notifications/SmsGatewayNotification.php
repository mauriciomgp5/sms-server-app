<?php

namespace App\Notifications;

use App\Broadcasting\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SmsGatewayNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $ip, public $device)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', WhatsappChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        //avisanod que gateway sms parou de responder ping
        return (new MailMessage)
            ->greeting(now()->timezone('America/Sao_Paulo')->hour < 12 ? 'Bom dia!' : (now()->timezone('America/Sao_Paulo')->hour < 18 ? 'Boa tarde!' : 'Boa noite!'))
            ->level('error')
            ->subject('Gateway SMS não responde')
            ->line('O Gateway SMS ' . $this->device . ' (' . $this->ip . ') não responde ao ping.')
            ->line('Verifique a conexão e o status do dispositivo.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return 'O Gateway SMS ' . $this->device . ' (' . $this->ip . ') não responde ao ping. Verifique a conexão e o status do dispositivo.';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
