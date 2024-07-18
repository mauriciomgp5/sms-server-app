<?php

namespace App\Filament\Resources;

use App\Enums\Config\TypeEnum;
use App\Filament\Resources\ConfigResource\Pages;
use App\Filament\Resources\ConfigResource\RelationManagers;
use App\Models\Config;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Leandrocfe\FilamentPtbrFormFields\Money;

class ConfigResource extends Resource
{
    protected static ?string $model = Config::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('data.type')
                    ->options(TypeEnum::class)
                    ->unique(ignoreRecord: true)
                    ->label('Nome'),
                Money::make('data.sale_cost')
                    ->label('Custo')
                    ->required(),
                Money::make('data.sale_price')
                    ->label('Preço')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data.type')
                    ->label('Tipo')
                    ->formatStateUsing(function ($state) {
                        return TypeEnum::from($state)->getLabel();
                    })
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data.sale_cost')
                    ->label('Custo')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('data.sale_price')
                    ->label('Venda')
                    ->money('BRL')
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
            'index' => Pages\ListConfigs::route('/'),
            'create' => Pages\CreateConfig::route('/create'),
            'edit' => Pages\EditConfig::route('/{record}/edit'),
        ];
    }
}
