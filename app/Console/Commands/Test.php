<?php

// app/Console/Commands/Test.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting update process');

        try {
            $this->runProcess('git pull', 'Fetching the last commit', 'Last commit fetched');
            $this->runProcess('php artisan migrate --force', 'Updating database', 'Database updated');
            $this->runProcess('composer install', 'Updating dependencies', 'Dependencies updated');
            $this->runProcess('npm install', 'Updating node modules', 'Node modules updated');
            $this->runProcess('npm run build', 'Building assets', 'Assets built');
            $this->runProcess('php artisan optimize', 'Optimizing application', 'Application optimized');
            
            $this->info('App updated successfully');
            Notification::make()
            ->success()
            ->title('Sistema atualizado')
            ->send();
        } catch (\Throwable $th) {
            $this->error('Update process failed: ' . $th->getMessage());
            Notification::make()
            ->error()
            ->title('Falha ao atualizar o sistema')
            ->body($th->getMessage())
            ->send();
        }
    }

    private function runProcess($command, $startMessage, $successMessage)
    {
        $this->info($startMessage);
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        $this->info($successMessage);
    }
}
