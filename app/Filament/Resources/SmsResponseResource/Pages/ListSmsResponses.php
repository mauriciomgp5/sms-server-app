<?php

namespace App\Filament\Resources\SmsResponseResource\Pages;

use App\Filament\Resources\SmsResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsResponses extends ListRecords
{
    protected static string $resource = SmsResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
