<?php

namespace App\Enums\Config;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TypeEnum: string implements HasColor, HasLabel
{

    case Pricing = 'pricing';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pricing => 'Preços',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Pricing => 'info',
        };
    }
}
