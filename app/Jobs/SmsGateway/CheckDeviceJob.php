<?php

namespace App\Jobs\SmsGateway;

use App\Models\User;
use App\Models\SmsGateway;
use Illuminate\Queue\SerializesModels;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\SmsGatewayNotification;

class CheckDeviceJob implements ShouldQueue
{
    use Queueable;

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
        $records = SmsGateway::get();

        info('Checking devices...');
        foreach ($records as $record) {

            $service = new SmsGatewayService($record);
            $resp = $service->checkConnection();
            if (isset($resp['status']) && $resp['status'] === 'ok') {
                $record->update(['is_active' => true, 'model' => $resp['model']]);
            } else {
                $record->update(['is_active' => false]);
                $usersNotifiable = User::where('is_notifiable', true)->get();
                foreach ($usersNotifiable as $user) {
                    $user->notify(new SmsGatewayNotification($record->ip_address, $record->name));
                }
            }
        }
        info('Devices checked.');
    }
}
