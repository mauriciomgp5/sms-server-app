<?php

namespace App\Services\Sms;

use App\Enums\Config\TypeEnum;
use App\Models\Company;
use App\Models\Config;
use App\Models\SmsLog;
use App\Models\SmsSlot;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    protected $request;

    public function __construct($gateway = null)
    {
        if ($gateway) {
            $urlBase = "{$gateway->ip_address}:{$gateway->port}";

            $this->request = Http::withBasicAuth($gateway->username, $gateway->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->baseUrl($urlBase);
        }
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
        try {
            $response = $this->request->get('/');
        } catch (\Throwable $th) {
            return [
                'error' => $th->getMessage(),
            ];
        }

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
            ->whereColumn('sent_count', '<', 'max_sends')
            ->orderBy('updated_at', 'asc')
            ->first();

        if (!$slot) {
            throw new \Exception('Nenhum slot disponível');
        }

        return $slot;
    }

    public function sendSms(string $message, $phoneNumbers, Company $company, $user)
    {
        try {
            $config = Config::where('data->type', TypeEnum::Pricing)->first();
            if (!$config) {
                return ['error' => 'Preços não configurado'];
            }
            // Obtém o próximo slot disponível
            $slot = $this->getNextAvailableSlot();

            $urlBase = "{$slot->gateway->ip_address}:{$slot->gateway->port}";

            $request = Http::withBasicAuth($slot->gateway->username, $slot->gateway->password)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->baseUrl($urlBase);
            $numbersValidate = [];
            foreach ($phoneNumbers as $phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) == 11) {
                    $phone = '55' . $phone;
                }
                $numbersValidate[] = $phone;
            }
            $response = $request
                ->post('message', [
                    'simNumber' => $slot->slot_number,
                    'message' => $message,
                    'phoneNumbers' => $numbersValidate,
                    'withDeliveryReport' => true,
                ]);

            if ($response->successful()) {
                info($response->body());
                $data = $response->json();
            } else {
                return ['error' => 'Erro: ' . $response->body()];
            }

            // Registrar o envio no banco de dados
            foreach ($phoneNumbers as $phone) {
                $smsLog = SmsLog::create([
                    'company_id' => $company->id,
                    'external_id' => $data['id'],
                    'gateway_id' => $slot->gateway_id,
                    'slot_id' => $slot->id,
                    'phone' => $phone,
                    'message' => $message,
                    'price' => $config->data['sale_price'],
                    'cost' => $config->data['sale_cost'],
                    'user_id' => $user->id,
                ]);
                // Atualizar contagem de envios
                $slot->increment('sent_count');
                $company->balance->decrement('balance', $config->data['sale_price']);
            }


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
