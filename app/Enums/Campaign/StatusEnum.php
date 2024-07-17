<?php

namespace App\Enums\Campaign;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Finished = 'finished';
    case Canceled = 'canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Finished => 'Finalizada',
            self::Canceled => 'Cancelada',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Finished => 'success',
            self::Canceled => 'danger',
        };
    }
}
