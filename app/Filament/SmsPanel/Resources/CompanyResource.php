<?php

namespace App\Filament\SmsPanel\Resources;

use App\Filament\SmsPanel\Resources\CompanyResource\Pages;
use App\Filament\SmsPanel\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $label = 'Empresa';
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('primary_document')
                    ->label('Cpf/Cnpj')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('secondary_document')
                    ->label('RG/IE')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->columnSpan(2)
                    ->email()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('logo')
                    ->directory('logos'),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255),
                Forms\Components\Repeater::make('phones')
                    ->label('Lista de telefones')
                    ->addActionLabel('Novo')
                    ->schema([
                        Forms\Components\TextInput::make('phones')
                            ->label('Telefone')
                            ->tel(),
                    ]),
                Forms\Components\Fieldset::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('zip_code')
                            ->mask('99999-999')
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('address')
                            ->label('Endereço')

                            ->required(),
                        Forms\Components\TextInput::make('number')
                            ->label('Número')

                            ->required(),
                        Forms\Components\TextInput::make('complement')

                            ->label('Complemento'),
                        Forms\Components\TextInput::make('neighborhood')

                            ->label('Bairro')
                            ->required(),
                        Forms\Components\TextInput::make('city')

                            ->label('Cidade')
                            ->required(),
                        Forms\Components\Select::make('state')

                            ->label('Estado')
                            ->options([
                                'AC' => 'Acre',
                                'AL' => 'Alagoas',
                                'AP' => 'Amapá',
                                'AM' => 'Amazonas',
                                'BA' => 'Bahia',
                                'CE' => 'Ceará',
                                'DF' => 'Distrito Federal',
                                'ES' => 'Espírito Santo',
                                'GO' => 'Goiás',
                                'MA' => 'Maranhão',
                                'MT' => 'Mato Grosso',
                                'MS' => 'Mato Grosso do Sul',
                                'MG' => 'Minas Gerais',
                                'PA' => 'Pará',
                                'PB' => 'Paraíba',
                                'PR' => 'Paraná',
                                'PE' => 'Pernambuco',
                                'PI' => 'Piauí',
                                'RJ' => 'Rio de Janeiro',
                                'RN' => 'Rio Grande do Norte',
                                'RS' => 'Rio Grande do Sul',
                                'RO' => 'Rondônia',
                                'RR' => 'Roraima',
                                'SC' => 'Santa Catarina',
                                'SP' => 'São Paulo',
                                'SE' => 'Sergipe',
                                'TO' => 'Tocantins',
                            ])
                            ->required(),
                    ])->columns(4)
            ])->columns([
                'default' => 4,
                'sm' => 1
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
