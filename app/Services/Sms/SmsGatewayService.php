<?php

namespace App\Services\Sms;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('SMS_GATEWAY_BASE_URL', 'http://192.168.1.43:8080');
    }

    public function getThreads($limit = 10, $offset = 0)
    {
        $response = Http::get("{$this->baseUrl}/v1/thread", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function getThreadMessages($threadId, $limit = 10, $offset = 0)
    {
        $response = Http::get("{$this->baseUrl}/v1/thread/{$threadId}", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function listSms($limit = 10, $offset = 0)
    {
        $response = Http::get("{$this->baseUrl}/v1/sms", [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }

    public function sendSms($phone, $message, $slot)
    {
        $gateway = $slot->gateway;

        if (!$slot) {
            return [
                'error' => 'Slot não encontrado',
            ];
        }

        if (!$gateway) {
            return [
                'error' => 'Gateway não encontrado',
            ];
        }

        if (!$slot->is_active) {
            return [
                'error' => 'Slot desativado',
            ];
        }

        if ($slot->sent_count >= $slot->max_sends) {
            return [
                'error' => 'Limite de envios atingido',
            ];
        }

        $query = [
            'phone' => $phone,
            'message' => $message,
            'sim_slot' => $slot->slot_number,
        ];

        try {
            //code...
            $url = "{$gateway->ip_address}:{$gateway->port}/v1/sms/?" . http_build_query($query);
            $response = Http::put($url);
        } catch (\Throwable $th) {
            return [
                'error' => $th->getMessage()
            ];
        }

        // Registrar o envio no banco de dados
        $smsLog = SmsLog::create([
            'gateway_id' => $gateway->id,
            'slot_id' => $slot->id,
            'phone' => $phone,
            'message' => $message,
        ]);

        // Atualizar contagem de envios
        $slot->increment('sent_count');

        return [
            'response' => $response->body(),
            'sms_log_id' => $smsLog->id,
        ];
    }


    public function getSms($smsId)
    {
        $response = Http::get("{$this->baseUrl}/v1/sms/{$smsId}");

        return $response->json();
    }

    public function getDeviceStatus()
    {
        $response = Http::get("{$this->baseUrl}/v1/device/status");

        return $response->json();
    }
}
