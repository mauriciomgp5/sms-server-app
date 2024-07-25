<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsGatewayResource\Pages;
use App\Filament\Resources\SmsGatewayResource\RelationManagers;
use App\Models\SmsGateway;
use App\Services\Sms\SmsGatewayService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsGatewayResource extends Resource
{
    protected static ?string $label = 'Dispositivo';

    protected static ?string $model = SmsGateway::class;

    protected static ?string $navigationGroup = 'SMS';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip_address')
                    ->label('Endereço IP')
                    ->ip()
                    ->required(),
                Forms\Components\TextInput::make('port')
                    ->label('Porta')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(65535)
                    ->required(),
                Forms\Components\TextInput::make('slots')
                    ->label('Slots')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Número de slots de Chip SIM disponíveis')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(65535)
                    ->required(),

                Forms\Components\TextInput::make('username')
                    ->label('Usuário')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Usuário para autenticação no gateway'),

                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->hintIcon('heroicon-o-information-circle')
                    ->hintIconTooltip('Senha para autenticação no gateway'),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label('Descrição')
                    ->maxLength(255),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Endereço IP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('port')
                    ->label('Porta')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('check-device')
                    ->action(function ($record) {
                        if (empty($record->username) || empty($record->password)) {
                            return Notification::make()
                                ->title('Erro')
                                ->body('Usuário e senha não configurados')
                                ->danger();
                        }
                        $service = new SmsGatewayService($record);
                        $resp = $service->checkConnection();
                        if (isset($resp['status']) && $resp['status'] === 'ok') {
                            $record->update(['is_active' => true, 'model' => $resp['model']]);
                            Notification::make()
                                ->title('Sucesso')
                                ->body('Gateway conectado com sucesso')
                                ->success()
                                ->send();
                        } else {
                            $record->update(['is_active' => false]);
                            Notification::make()
                                ->title('Erro')
                                ->body('Falha ao conectar com o gateway')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('list-webhooks')
                        ->label('Webhooks')
                        ->action(function ($record) {
                            $service = new SmsGatewayService($record);
                            dd($service->getWebhooks());
                        }),
                    //register webhooks
                    Tables\Actions\Action::make('register-webhooks')
                        ->label('Registrar Webhooks')
                        ->form([
                            Forms\Components\TextInput::make('url')
                                ->label('URL')
                                ->required(),
                            Forms\Components\Select::make('event')
                                ->label('Evento')
                                ->options([
                                    'sms:received' => 'SMS Recebido',
                                ])
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            $service = new SmsGatewayService($record);
                            $resp = $service->registerWebhook($record->id, $data['url'], $data['event']);
                            if (isset($resp['error'])) {
                                Notification::make()
                                    ->title('Erro')
                                    ->body($resp['error'])
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Sucesso')
                                    ->body('Webhook registrado com sucesso')
                                    ->success()
                                    ->send();
                            }
                        }),
                    //delete webhook
                    Tables\Actions\Action::make('delete-webhook')
                        ->label('Deletar Webhook')
                        ->form([
                            Forms\Components\TextInput::make('webhook_id')
                                ->label('Webhook')
                                ->required(),
                        ])
                        ->action(function ($record, $data) {
                            $service = new SmsGatewayService($record);
                            $resp = $service->deleteWebhook($data['webhook_id']);
                            if (isset($resp['error'])) {
                                Notification::make()
                                    ->title('Erro')
                                    ->body($resp['error'])
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Sucesso')
                                    ->body('Webhook deletado com sucesso')
                                    ->success()
                                    ->send();
                            }
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SlotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsGateways::route('/'),
            'create' => Pages\CreateSmsGateway::route('/create'),
            'edit' => Pages\EditSmsGateway::route('/{record}/edit'),
        ];
    }
}
