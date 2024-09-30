<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $message = $notification->toWhatsapp($notifiable);
        $phoneNumber = $notifiable->phone_number;

        if (!$phoneNumber) {
            return;
        }

        try {
            $resp = Http::post('https://atendimento.mgpsistemas.com.br/api/send-whatsapp', [
                'phone' => $phoneNumber,
                'message' => $message,
                'token' => config('services.api.token-atendimento'),
            ]);
            info($resp->json());
        } catch (\Throwable $th) {
            //throw $th;
            info($th->getMessage());
        }

    }
}
