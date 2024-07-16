<?php

namespace App\Filament\SmsPanel\Resources;

use App\Filament\SmsPanel\Resources\SmsLogResource\Pages;
use App\Filament\SmsPanel\Resources\SmsLogResource\RelationManagers;
use App\Models\SmsLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsLogResource extends Resource
{
    protected static ?string $label = 'Envio';
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('external_id')
                    ->label('ID Externo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('gateway_id')
                    ->label('Gateway')
                    ->relationship('gateway', 'name')
                    ->required(),
                Forms\Components\Select::make('slot_id')
                    ->label('Slot')
                    ->relationship('slot', 'id')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('Mensagem')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->label('Enviado em'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado em')
                    ->dateTime()
                    ->sortable(),
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
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Excluir selecionados'),
                ]),
            ]);
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
            'create' => Pages\CreateSmsLog::route('/create'),
            'edit' => Pages\EditSmsLog::route('/{record}/edit'),
        ];
    }
}
