<?php

namespace App\Models;

use App\Enums\SmsLog\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsLog extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => StatusEnum::class,
        'created_at' => 'datetime:d/m/Y H:i:s',
        'updated_at' => 'datetime:d/m/Y H:i:s',
    ];

    public function response(): HasMany
    {
        return $this->hasMany(SmsResponse::class, 'sms_log_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(SmsSlot::class, 'slot_id');
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(SmsGateway::class, 'sms_gateway_id');
    }
}
