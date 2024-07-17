<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    public function companyPurchase()
    {
        return $this->hasOne(CompanyPurchase::class);
    }

    public function companyBalance()
    {
        return $this->hasOne(CompanyBalance::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function users()
    {
        return $this->hasOne(User::class);
    }
}
