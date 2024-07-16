<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\SmsSlot;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Services\Sms\SmsGatewayService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SmsSlotResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SmsSlotResource\RelationManagers;
use Filament\Forms\Get;

class SmsSlotResource extends Resource
{
    protected static ?string $label = 'Sim Card';
    protected static ?string $model = SmsSlot::class;
    protected static ?string $tenantOwnershipRelationshipName = 'gateway';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'SMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('gateway_id')
                    ->relationship('gateway', 'name')
                    ->live()
                    ->required(),

                Forms\Components\TextInput::make('slot_number')
                    ->label('Número do Slot')
                    ->visible(fn (Get $get) => $get('gateway_id') !== null)
                    ->unique(modifyRuleUsing: function ($rule, Get $get) {
                        return $rule->where('gateway_id', $get('gateway_id'));
                    }, ignoreRecord: true)
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->required(),
                Forms\Components\TextInput::make('sent_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('max_sends')
                    ->required()
                    ->numeric()
                    ->default(800),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('gateway.name')
                    ->label('Gateway')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot_number')
                    ->label('Número do Slot')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Enviados')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_sends')
                    ->label('Máximo de Envios')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Número de Telefone')
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
                Tables\Actions\Action::make('test-send')
                    ->modal()
                    ->modalWidth('sm')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->hintIcon('heroicon-o-phone')
                            ->hintIconTooltip('Informe o número de telefone no formato 5511999999999')
                            ->default('5517996165851')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('message')
                            ->label('Mensagem')
                            ->required()
                            ->default('Teste de envio de mensagem')
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, $record) {
                        $sgs = new SmsGatewayService($record->gateway);
                        $resp = $sgs->sendSms($data['message'], ['+' . $data['phone']], $record);
                        if (isset($resp['error'])) {
                            Notification::make()
                                ->title('Erro')
                                ->body($resp['error'])
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Sucesso')
                                ->body('Mensagem enviada com sucesso')
                                ->success()
                                ->send();
                        }
                    })
                    ->label('Enviar Teste')
                    ->icon('heroicon-o-envelope-open')
                    ->color('warning'),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsSlots::route('/'),
            'create' => Pages\CreateSmsSlot::route('/create'),
            'edit' => Pages\EditSmsSlot::route('/{record}/edit'),
        ];
    }
}
