<?php

namespace App\Jobs\Sms;

use App\Models\SmsLog;
use Illuminate\Bus\Queueable;
use App\Enums\SmsLog\StatusEnum;
use Illuminate\Queue\SerializesModels;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckPendingMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('check pending messages');
        foreach (SmsLog::where('status', 'pending')->get() as $record) {
            $smsService = new SmsGatewayService($record->slot->gateway);
            $response = $smsService->getMessageStatus($record->external_id);
            if (isset($response['state']) && $response['state'] === 'Delivered') {
                info('update status to delivered by job');
                if ($record->status !== StatusEnum::Responsed) {
                    $record->update(['status' => StatusEnum::Delivered]);
                }
            } elseif (isset($response['state']) && $response['state'] === 'Sent') {
                info('update status to sent by job');
                if ($record->status !== StatusEnum::Responsed) {
                    $record->update(['status' => StatusEnum::Sent]);
                }
            } elseif (isset($response['state']) && $response['state'] === 'Pending') {
                info('update status to pending by job');
                // Do nothing
            } else {
                info('update status to failed by job');
                $record->update(['status' => StatusEnum::Failed]);
            }
        }
    }
}
