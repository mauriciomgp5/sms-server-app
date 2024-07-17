<?php

namespace App\Filament\Resources\CompanyPurchaseResource\Pages;

use App\Enums\CompanyPurchase\StatusEnum;
use App\Filament\Resources\CompanyPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyPurchase extends CreateRecord
{
    protected static string $resource = CompanyPurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['description'])) {
            $data['description'] = 'Compra de ' . $data['amount'] . ' reais em créditos';
        }

        return $data;
    }

    protected function afterCreate()
    {
        if ($this->record->status === StatusEnum::Paid) {
            if ($this->record->company->balance === null) {
                $this->record->company->balance()->create([
                    'balance' => $this->record->amount,
                ]);
            } else {
                $this->record->company->balance->increment('balance', $this->record->amount);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
