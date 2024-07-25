<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\SmsLog;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\SmsLog\StatusEnum;
use Filament\Resources\Resource;
use App\Services\Sms\SmsGatewayService;
use Filament\Notifications\Notification;
use App\Filament\Resources\SmsLogResource\Pages;

class SmsLogResource extends Resource
{
    protected static ?string $label = 'Envio';
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'SMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('gateway_id')
                    ->label('Gateway')
                    ->required(),
                Forms\Components\TextInput::make('slot_id')
                    ->label('Slot')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('Mensagem')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slot.gateway.name')
                    ->label('Dispositivo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.slot_number')
                    ->label('Slot')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('Mensagem')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('external_id')
                    ->label('ID Externo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('check-status')
                    ->label('Check Status')
                    ->action(function ($record) {
                        $smsService = new SmsGatewayService($record->slot->gateway);
                        $response = $smsService->getMessageStatus($record->external_id);
                        if (isset($response['state']) && $response['state'] === 'Delivered') {
                            if ($record->status !== StatusEnum::Responsed) {
                                $record->update(['status' => StatusEnum::Delivered]);
                            }
                            Notification::make()
                                ->title('Status Atualizado')
                                ->body('Status da mensagem atualizado.')
                                ->success()
                                ->send();
                        } elseif (isset($response['state']) && $response['state'] === 'Pending') {
                            Notification::make()
                                ->title('Pendente')
                                ->body('Preparando para envio.')
                                ->warning()
                                ->send();
                        } elseif (isset($response['state']) && $response['state'] === 'Sent') {
                            if ($record->status !== StatusEnum::Responsed) {
                                $record->update(['status' => StatusEnum::Sent]);
                            }
                            Notification::make()
                                ->title('Processado')
                                ->body('Mensagem enviada, mas sem relatos de entrega.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Erro')
                                ->body(json_encode($response))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
            // 'create' => Pages\CreateSmsLog::route('/create'),
            'edit' => Pages\EditSmsLog::route('/{record}/edit'),
        ];
    }
}
