<?php

use App\Jobs\Sms\CheckPendingMessages;
use App\Jobs\SmsGateway\CheckDeviceJob;
use Illuminate\Support\Facades\Schedule;


Schedule::job(new CheckPendingMessages)->everyMinute();

Schedule::job(new CheckDeviceJob())->everyTenMinutes();
