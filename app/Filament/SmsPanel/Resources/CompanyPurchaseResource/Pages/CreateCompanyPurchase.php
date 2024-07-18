<?php

namespace App\Filament\SmsPanel\Resources\CompanyPurchaseResource\Pages;

use App\Enums\CompanyPurchase\StatusEnum;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\SmsPanel\Resources\CompanyPurchaseResource;

class CreateCompanyPurchase extends CreateRecord
{
    protected static string $resource = CompanyPurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['description'] = 'Compra de ' . $data['amount'] . ' em créditos';
        $data['status'] = StatusEnum::Pending;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
