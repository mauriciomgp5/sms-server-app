<?php

namespace App\Filament\SmsPanel\Resources\CompanyPurchaseResource\Pages;

use App\Filament\SmsPanel\Resources\CompanyPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyPurchase extends EditRecord
{
    protected static string $resource = CompanyPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
