<?php

namespace App\Enums\CompanyPurchase;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasColor, HasLabel
{

    case Paid = 'paid';
    case Pending = 'pending';
    case Canceled = 'canceled';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::Paid => 'Pago',
            self::Pending => 'Pendente',
            self::Canceled => 'Cancelado',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending => 'warning',
            self::Canceled => 'danger',
        };
    }
}
