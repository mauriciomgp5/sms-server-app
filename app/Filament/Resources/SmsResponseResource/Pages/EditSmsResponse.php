<?php

namespace App\Filament\Resources\SmsResponseResource\Pages;

use App\Filament\Resources\SmsResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsResponse extends EditRecord
{
    protected static string $resource = SmsResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
