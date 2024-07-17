<?php

namespace App\Filament\SmsPanel\Resources\CompanyPurchaseResource\Pages;

use App\Filament\SmsPanel\Resources\CompanyPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyPurchase extends CreateRecord
{
    protected static string $resource = CompanyPurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['description'] = 'Compra de ' . $data['amount'] . ' em créditos';
        return $data;
    }
}
