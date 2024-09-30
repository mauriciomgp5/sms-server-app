<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogsClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:logs-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear logs files every 5 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Caminho para o diretório de logs
        $logPath = storage_path('logs');

        // Lista de arquivos de log a serem limpos
        $logFiles = [
            'laravel.log',
            'horizon.log',
            'reverb.log',
        ];

        foreach ($logFiles as $file) {
            $filePath = $logPath . DIRECTORY_SEPARATOR . $file;

            if (File::exists($filePath)) {
                // Limpa o conteúdo do arquivo de log
                File::put($filePath, '');
                $this->info("Arquivo {$file} limpo com sucesso.");
            } else {
                $this->warn("Arquivo {$file} não encontrado.");
            }
        }
    }
}
