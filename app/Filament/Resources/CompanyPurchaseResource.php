<?php

namespace App\Filament\Resources;

use App\Enums\CompanyPurchase\StatusEnum;
use App\Filament\Resources\CompanyPurchaseResource\Pages;
use App\Filament\Resources\CompanyPurchaseResource\RelationManagers;
use App\Models\CompanyPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

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
                    ->required()
                    ->searchable()
                    ->options(fn () => \App\Models\Company::pluck('name', 'id')),
                    
                Forms\Components\Select::make('user_id')
                    ->label('Usuário')
                    ->required()
                    ->searchable()
                    ->options(fn () => \App\Models\User::pluck('name', 'id')),
                Forms\Components\Select::make('amount')
                    ->label('Valor')
                    ->required()
                    ->options([
                        '50' => 'R$ 50,00',
                        '100' => 'R$ 100,00',
                        '200' => 'R$ 200,00',
                        '300' => 'R$ 300,00',
                        '400' => 'R$ 400,00',
                        '500' => 'R$ 500,00',
                        '1000' => 'R$ 1.000,00',
                        '2000' => 'R$ 2.000,00',
                        '3000' => 'R$ 3.000,00',
                    ]),
                Forms\Components\TextInput::make('description')
                    ->label('Descrição')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->required()
                    ->default('pending')
                    ->options(StatusEnum::class)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprovar')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status === StatusEnum::Pending)
                    ->requiresConfirmation()
                    ->modalHeading('Aprovar compra?')
                    ->action(function ($record) {
                        DB::beginTransaction();
                        if ($record->company->balance === null) {
                            $record->company->balance()->create([
                                'balance' => $record->amount,
                            ]);
                        } else {
                            $record->company->balance->increment('balance', $record->amount);
                        }
                        $record->update(['status' => StatusEnum::Paid]);
                        DB::commit();
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
