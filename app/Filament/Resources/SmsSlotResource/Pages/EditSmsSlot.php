<?php

namespace App\Filament\Resources\SmsSlotResource\Pages;

use App\Filament\Resources\SmsSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsSlot extends EditRecord
{
    protected static string $resource = SmsSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
