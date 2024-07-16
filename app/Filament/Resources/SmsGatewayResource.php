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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsGatewayResource extends Resource
{
    protected static ?string $label = 'Gateway';
    protected static ?string $pluralLabel = 'Gateways';

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
                        dd($service->getDevice());
                    }),
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
