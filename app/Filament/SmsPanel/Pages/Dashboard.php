<?php

namespace App\Filament\SmsPanel\Pages;

use App\Models\Company;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Session;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('selectCompany')
                            ->label('Empresa Ativa')
                            ->selectablePlaceholder(false)
                            ->options(
                                Company::whereHas(
                                    'users',
                                    fn ($query) =>
                                    $query->where('user_id', auth()->id())
                                )->pluck('name', 'id')
                            )->default(session('company')->id),
                        DatePicker::make('startDate')
                            ->displayFormat('d/m/Y')
                            ->label('Data Inicial')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->label('Data Final')
                            ->displayFormat('d/m/Y')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),
                    ])
                    ->columns(3),
            ]);
    }

    public function mount()
    {
        Session::put('company', Company::whereHas(
            'users',
            fn ($query) =>
            $query->where('user_id', auth()->id())
        )->first());
    }
}
