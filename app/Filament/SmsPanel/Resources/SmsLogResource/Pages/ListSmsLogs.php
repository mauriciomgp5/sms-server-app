<?php

namespace App\Filament\SmsPanel\Resources\SmsLogResource\Pages;

use App\Filament\SmsPanel\Resources\SmsLogResource;
use App\Models\Company;
use App\Services\Sms\SmsGatewayService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_sms')
                ->label('Enviar SMS')
                ->icon('heroicon-o-phone')
                ->modal()
                ->modalWidth('md')
                ->form([
                    Forms\Components\Select::make('company_id')
                        ->label('Empresa')
                        ->options(Company::whereHas('users', function ($query) {
                            $query->where('user_id', auth()->id());
                        })->pluck('name', 'id'))
                        ->default(Company::whereHas('users', function ($query) {
                            $query->where('user_id', auth()->id());
                        })->first()->id)
                        ->searchable()
                        ->required(),
                    PhoneNumber::make('phone')
                        ->label('Telefone')
                        ->required(),
                    Forms\Components\Textarea::make('message')
                        ->label('Mensagem')
                        ->required(),
                ])
                ->action(function ($data) {
                    $company = Company::find($data['company_id']);
                    if ($company->balance?->balance > 0) {
                        $sgs = new SmsGatewayService();
                        $resp = $sgs->sendSms($data['message'], [$data['phone']], $company, auth()->user());

                        if (isset($resp['error'])) {
                            Notification::make()
                                ->title('Erro ao enviar SMS')
                                ->body($resp['error'])
                                ->danger()
                                ->send();
                            return;
                        }

                        Notification::make()
                            ->title('SMS enviado')
                            ->body('A mensagem foi enviada para ' . $data['phone'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Saldo insuficiente')
                            ->body('A empresa ' . $company->name . ' não possui saldo suficiente para enviar SMS.')
                            ->danger()
                            ->send();
                    }
                })


        ];
    }
}
