<?php

namespace App\Enums\SmsLog;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasColor, HasLabel
{

    case Sent = 'sent';
    case Failed = 'failed';
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Responsed = 'responsed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Sent => 'Enviado',
            self::Failed => 'Falhou',
            self::Pending => 'Pendente',
            self::Delivered => 'Entregue',
            self::Responsed => 'Respondido',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::Sent => 'success',
            self::Failed => 'danger',
            self::Pending => 'warning',
            self::Delivered => 'success',
            self::Responsed => 'info',
        };
    }
}
