<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Company;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Leandrocfe\FilamentPtbrFormFields\Cep;
use Leandrocfe\FilamentPtbrFormFields\Document;
use App\Filament\Resources\CompanyResource\Pages;
use Leandrocfe\FilamentPtbrFormFields\PhoneNumber;

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
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Document::make('primary_document')
                    ->label('Cpf/Cnpj')
                    ->dynamic()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('secondary_document')
                    ->label('RG/IE')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->columnSpan(2)
                    ->email()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->directory('logos'),
                Forms\Components\TextInput::make('website')
                    ->label('Website')
                    ->maxLength(255),
                Forms\Components\Repeater::make('phones')
                    ->label('Lista de telefones')
                    ->addActionLabel('Novo')
                    ->schema([
                        PhoneNumber::make('phone')
                            ->label('Telefone')
                            ->tel(),
                    ]),
                Forms\Components\Fieldset::make('Endereço')
                    ->schema([
                        Cep::make('zip_code')
                            ->label('CEP')
                            ->viaCep(
                                mode: 'suffix',
                                errorMessage: 'CEP inválido.',
                                setFields: [
                                    'address' => 'logradouro',
                                    'address_number' => 'numero',
                                    'complement' => 'complemento',
                                    'neighborhood' => 'bairro',
                                    'city' => 'localidade',
                                    'state' => 'uf'
                                ]
                            ),
                        Forms\Components\TextInput::make('address')
                            ->label('Endereço')
                            ->required(),
                        Forms\Components\TextInput::make('address_number')
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
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')
                    ->label('Logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
                    ->label('Website')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Endereço')
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
                Tables\Actions\Action::make('create-token')
                    ->label('Criar token')
                    ->icon('heroicon-o-key')
                    ->modal()
                    ->modalDescription('Caso exista um token de acesso, ele será substituído.')
                    ->requiresConfirmation()
                    ->modalWidth('md')
                    ->action(function ($record) {
                        $userApi = $record->users()->where('type', 'api')->first();
                        if (!$userApi) {
                            $userApi = $record->users()->create([
                                'name' => 'API',
                                'email' => 'api@' . $record->primary_document,
                                'password' => bcrypt(Str::random(10)),
                                'type' => 'api',
                            ]);
                        }
                        Notification::make()
                            ->title('Token criado')
                            ->body('O token de acesso da API é: ' . $userApi->createToken('api')->plainTextToken)
                            ->success()
                            ->persistent()
                            ->actions([
                                Action::make('copy')
                                    ->button()
                                    ->label('Copiar token')
                                    ->icon('heroicon-o-clipboard')
                                    ->action(function () {
                                        dd('oi');
                                    }),
                                Action::make('close')
                                    ->label('Fechar')
                                    ->color('gray')
                                    ->close(),
                            ])
                            ->send();
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
