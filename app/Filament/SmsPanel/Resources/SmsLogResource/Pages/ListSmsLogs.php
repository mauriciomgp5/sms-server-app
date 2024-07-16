<?php

namespace App\Filament\SmsPanel\Resources\SmsLogResource\Pages;

use App\Filament\SmsPanel\Resources\SmsLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
