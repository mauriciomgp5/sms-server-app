<?php

namespace App\Filament\Resources\ConfigResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ConfigResource;

class ListConfigs extends ListRecords
{
    protected static string $resource = ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updateProject')
                ->label('Atualizar Sistema')
                ->action(function () {
                    $output = [];
                    $return_var = 0;

                    // Executar o script de atualização
                    exec('/var/www/update.sh', $output, $return_var);

                    // Verificar o status da execução
                    if ($return_var === 0) {
                        Notification::make()
                            ->title('Sucesso')
                            ->body('Projeto atualizado com sucesso!')
                            ->success()
                            ->send();
                        dd($output);
                    } else {
                        Notification::make()
                            ->title('Erro')
                            ->body('Falha ao atualizar o projeto. Verifique os logs.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
