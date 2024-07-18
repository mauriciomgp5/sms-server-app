<?php

namespace App\Models;

use App\Enums\Config\TypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];
}
