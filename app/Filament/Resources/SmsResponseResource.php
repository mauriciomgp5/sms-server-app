<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsResponseResource\Pages;
use App\Models\SmsResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsResponseResource extends Resource
{
    protected static ?string $label = 'Resposta';
    protected static ?string $model = SmsResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'SMS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sms_log_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('response')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->label('Resposta')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('smsLog.message')
                    ->label('Mensagem enviada')
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsResponses::route('/'),
            'create' => Pages\CreateSmsResponse::route('/create'),
            'edit' => Pages\EditSmsResponse::route('/{record}/edit'),
        ];
    }
}
