<?php

use App\Jobs\Sms\CheckPendingMessages;
use Illuminate\Support\Facades\Schedule;


Schedule::job(new CheckPendingMessages)->everyMinute();
