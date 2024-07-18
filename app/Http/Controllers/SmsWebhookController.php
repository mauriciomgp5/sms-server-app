<?php

namespace App\Http\Controllers;

use App\Enums;
use App\Models\SmsLog;
use App\Models\SmsResponse;
use Illuminate\Http\Request;
use App\Services\Sms\SmsGatewayService;

class SmsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request
            ->validate([
                'phone' => 'required|string',
                'message' => 'required|string',
            ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
                'errors' => [
                    'user' => ['Usuário não autenticado']
                ]
            ], 401);
        }

        $company = $user->companies->first();
        if (!$company) {
            return response()->json([
                'message' => 'Unauthorized',
                'errors' => [
                    'company' => ['Usuário não possui empresa']
                ]
            ], 401);
        }
        if ($company->balance?->balance > 0) {
            $sgs = new SmsGatewayService();
            $resp = $sgs->sendSms($data['message'], [$data['phone']], $company, auth()->user());

            if (isset($resp['error'])) {
                return response()->json([
                    'message' => 'Erro ao enviar SMS',
                    'errors' => [
                        'sms' => [$resp['error']]
                    ]
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'Saldo insuficiente',
                'errors' => [
                    'sms' => ['Saldo insuficiente']
                ]
            ], 500);
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'phone' => $data['phone'],
                'message' => $data['message']
            ]
        ]);
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
