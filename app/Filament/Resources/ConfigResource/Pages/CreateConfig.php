<?php

namespace App\Filament\Resources\ConfigResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\ConfigResource;
use App\Models\Config;
use Filament\Resources\Pages\CreateRecord;

class CreateConfig extends CreateRecord
{
    protected static string $resource = ConfigResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $model = Config::first();
        if ($model) {
            $model->update($data);
            return $model;
        }
        return static::getModel()::create($data);
    }
}
