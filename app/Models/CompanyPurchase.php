<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\CompanyPurchase\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyPurchase extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => StatusEnum::class,
        'data' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
