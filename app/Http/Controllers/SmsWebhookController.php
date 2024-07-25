<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsLog;
use App\Models\SmsResponse;
use App\Enums;

class SmsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        //validate
        $data = $request->validate([
            'deviceId' => 'required|string',
            'event' => 'required|string',
            'id' => 'required|string',
            'payload' => 'required|array',
            'payload.message' => 'required|string',
            'payload.phoneNumber' => 'required|string',
            'payload.receivedAt' => 'required|date',
            'webhookId' => 'required|string',
        ]);

        $smsLog = SmsLog::where('phone', self::normalizePhoneNumber($data['payload']['phoneNumber']))
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$smsLog) {
            return response()->json(['status' => 'error', 'message' => 'SMS log not found'], 404);
        }

        SmsResponse::create([
            'sms_log_id' => $smsLog->id,
            'message' => $data['payload']['message'],
        ]);
        $smsLog->update([
            'status' => Enums\SmsLog\StatusEnum::Responsed,
        ]);

        return response()->json(['status' => 'success']);
    }

    private static function normalizePhoneNumber($phoneNumber)
    {
        // Verifica se o número começa com 0
        if (substr($phoneNumber, 0, 1) === '0') {
            // Remove o 0 inicial e adiciona +55
            $phoneNumber = '+55' . substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }
}
