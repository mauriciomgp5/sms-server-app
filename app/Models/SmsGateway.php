<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsGateway extends Model
{
    use HasFactory;

    protected $casts = [
        'is_active' => 'boolean',
        'data' => 'array',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SmsSlot::class, 'gateway_id');
    }
}
