<?php

namespace App\Filament\SmsPanel\Resources\CompanyResource\Pages;

use App\Filament\SmsPanel\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function afterCreate(): void
    {
        $this->record->users()->attach(auth()->id());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
