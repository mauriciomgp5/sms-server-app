<?php

namespace App\Enums\User;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TypeUserEnum: string implements HasColor, HasLabel
{

    case User = 'user';
    case Api = 'api';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::User => 'Usuário',
            self::Api => 'API',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::User => 'success',
            self::Api => 'info',
        };
    }
}
