<?php

namespace App\Filament\SmsPanel\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmsBalance extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Enviados', SmsLog::where('status', 'sent')->where('company_id', session('company')?->id)->count()),
            Stat::make('Respostas', '21%'),
            Stat::make('Saldo', 'R$ ' . auth()->user()->companies->first()->balance->balance),
        ];
    }
}
