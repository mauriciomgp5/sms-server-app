<?php

namespace App\Filament\Resources\SmsGatewayResource\RelationManagers;

use App\Services\Sms\SmsGatewayService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SlotsRelationManager extends RelationManager
{
    protected static ?string $title = 'Slots';
    protected static string $relationship = 'slots';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('slot_number')
                    ->label('Número do Slot')
                    ->unique(modifyRuleUsing: function ($rule) {
                        return $rule->where('gateway_id', $this->getOwnerRecord()->id);
                    })
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Forms\Components\TextInput::make('max_sends')
                    ->label('Máximo de Envios')
                    ->required()
                    ->numeric()
                    ->default(800),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->unique()
                    ->required()
                    ->maxLength(255),
            ])->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('slot_number')
            ->columns([
                Tables\Columns\TextColumn::make('slot_number')
                    ->label('Número do Slot'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo'),
                Tables\Columns\TextColumn::make('max_sends')
                    ->label('Máximo de Envios'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Número de Telefone'),
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Envios Realizados'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i:s')
                    ->label('Criado em'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Slot'),
            ])
            ->actions([
                Tables\Actions\Action::make('test-send')
                    ->modal()
                    ->modalWidth('sm')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('message')
                            ->label('Mensagem')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, $record) {
                        $sgs = new SmsGatewayService();
                        $resp = $sgs->sendSms($data['phone'], $data['message'], $record);
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
