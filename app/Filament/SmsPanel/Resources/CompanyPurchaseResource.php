<?php

namespace App\Filament\SmsPanel\Resources;

use App\Filament\SmsPanel\Resources\CompanyPurchaseResource\Pages;
use App\Filament\SmsPanel\Resources\CompanyPurchaseResource\RelationManagers;
use App\Models\Company;
use App\Models\CompanyPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyPurchaseResource extends Resource
{
    protected static ?string $label = 'Compra';

    protected static ?string $model = CompanyPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Empresa')
                    ->options(Company::whereHas('user')->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\Select::make('amount')
                    ->label('Investimento')
                    ->required()
                    ->options([
                        '50' => 'R$ 50,00',
                        '100' => 'R$ 100,00',
                        '200' => 'R$ 200,00',
                        '500' => 'R$ 500,00',
                        '1000' => 'R$ 1.000,00',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(CompanyPurchase::query()->whereHas('company.users', function (Builder $query) {
                $query->where('users.id', auth()->id());
            }))
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
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
            'index' => Pages\ListCompanyPurchases::route('/'),
            'create' => Pages\CreateCompanyPurchase::route('/create'),
            'edit' => Pages\EditCompanyPurchase::route('/{record}/edit'),
        ];
    }
}
