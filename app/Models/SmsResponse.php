<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsResponse extends Model
{
    use HasFactory;
    public function smsLog()
    {
        return $this->belongsTo(SmsLog::class, 'sms_log_id');
    }
}
