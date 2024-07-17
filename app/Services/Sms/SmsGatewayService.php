<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use App\Models\SmsSlot;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    protected $request;

    public function __construct($gateway)
    {
        $urlBase = "{$gateway->ip_address}:{$gateway->port}";

        $this->request = Http::withBasicAuth($gateway->username, $gateway->password)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])->baseUrl($urlBase);
    }

    public function sendSmsBySlot($message, $phoneNumbers, $slot)
    {
        try {
            $response = $this->request
                ->post('message', [
                    'simNumber' => $slot->slot_number,
                    'message' => $message,
                    'phoneNumbers' => $phoneNumbers,
                    'withDeliveryReport' => true,
                ]);

            if ($response->successful()) {
                info($response->body());
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
                'gateway_id' => $slot->gateway_id,
                'slot_id' => $slot->id,
                'phone' => $phone,
                'message' => $message,
            ]);
            // Atualizar contagem de envios
            $slot->increment('sent_count');
        }

        return [
            'response' => $response->body(),
            'sms_log_id' => $smsLog->id,
        ];
    }

    public function getDevice()
    {
        $response = $this->request->get('health');

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

    public function checkConnection()
    {
        $response = $this->request->get('/');

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

    public function getWebhooks()
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

    public function registerWebhook($id, $webhookUrl, $events)
    {
        $response = $this->request->post('webhooks', [
            'id' => $id,
            'url' => $webhookUrl,
            'event' => $events,
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

    public function getNextAvailableSlot()
    {
        // Seleciona o próximo slot que não atingiu o limite de envios mensais
        $slot = SmsSlot::where('is_active', 1)
            ->where('sent_count', '<', 'max_sends')
            ->orderBy('updated_at', 'asc')
            ->first();

        if (!$slot) {
            throw new \Exception('No available slots for sending SMS.');
        }

        return $slot;
    }

    public function sendSms($message, $phoneNumbers)
    {
        try {
            // Obtém o próximo slot disponível
            $slot = $this->getNextAvailableSlot();

            $response = $this->request
                ->post('message', [
                    'simNumber' => $slot->slot_number,
                    'message' => $message,
                    'phoneNumbers' => $phoneNumbers,
                    'withDeliveryReport' => true,
                ]);

            if ($response->successful()) {
                info($response->body());
                $data = $response->json();
            } else {
                return response()->json(['error' => 'Failed to send SMS'], $response->status());
            }

            // Registrar o envio no banco de dados
            foreach ($phoneNumbers as $phone) {
                $smsLog = SmsLog::create([
                    'external_id' => $data['id'],
                    'gateway_id' => $slot->gateway_id,
                    'slot_id' => $slot->id,
                    'phone' => $phone,
                    'message' => $message,
                ]);
                // Atualizar contagem de envios
                $slot->increment('sent_count');
            }

            // Atualizar o timestamp do último envio
            $slot->update(['last_sent_at' => now()]);

            return [
                'response' => $response->body(),
                'sms_log_id' => $smsLog->id,
            ];
        } catch (\Throwable $th) {
            return [
                'error' => $th->getMessage()
            ];
        }
    }
}
