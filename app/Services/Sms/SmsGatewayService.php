<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    protected $request;
    protected $slot;

    public function __construct($gateway)
    {
        $urlBase = "{$gateway->ip_address}:{$gateway->port}";

        $this->request = Http::withBasicAuth($gateway->username, $gateway->password)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->baseUrl($urlBase);
    }

    public function sendSms($message, $phoneNumbers)
    {
        try {
            $response = $this->request
                ->post('message', [
                    'message' => $message,
                    'phoneNumbers' => $phoneNumbers,
                ]);

            if ($response->successful()) {
                $data = $response->json();
            } else {
                return response()->json(['error' => 'Failed to send SMS'], $response->status());
            }
        } catch (\Throwable $th) {
            return [
                'error' => $th->getMessage()
            ];
        }

        // Registrar o envio no banco de dados
        foreach ($phoneNumbers as $phone) {
            $smsLog = SmsLog::create([
                'external_id' => $data['id'],
                'gateway_id' => $this->slot->gateway_id,
                'slot_id' => $this->slot->id,
                'phone' => $phone,
                'message' => $message,
            ]);
            // Atualizar contagem de envios
            $this->slot->increment('sent_count');
        }

        return [
            'response' => $response->body(),
            'sms_log_id' => $smsLog->id,
        ];
    }

    public function getDevice()
    {
        $response = $this->request->get('device');

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to fetch devices',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }

    public function getMessageStatus($id)
    {
        $response = $this->request->get("message/{$id}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to fetch message status',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }

    public function healthCheck()
    {
        $response = $this->request->get('health');

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to check health status',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }

    public function listWebhooks()
    {
        $response = $this->request->get('webhooks');

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to list webhooks',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }

    public function registerWebhook($webhookUrl, $events)
    {
        $response = $this->request->post('webhooks', [
            'url' => $webhookUrl,
            'events' => $events,
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to register webhook',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }

    public function deleteWebhook($id)
    {
        $response = $this->request->delete("webhooks/{$id}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => 'Failed to delete webhook',
                'status' => $response->status(),
                'response' => $response->json(),
            ];
        }
    }
}
