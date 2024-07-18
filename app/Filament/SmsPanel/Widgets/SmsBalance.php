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
            //calculo das respostas em porcentagem
            Stat::make('Respostas', SmsLog::where('status', 'response')->where('company_id', session('company')?->id)->count()),
            Stat::make('Saldo', auth()->user()->companies->first()?->balance->balance ? 'R$ ' . number_format(auth()->user()->companies->first()?->balance->balance, 2, ',', '.') : 'R$ 0,00'),
        ];
    }
}
