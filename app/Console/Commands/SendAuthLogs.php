<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAuthLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:send-auth';
    protected $description = 'Send auth mail';

    public function handle()
    {
        $date = Carbon::yesterday()->format('Y-m-d');
        $logFilePath = storage_path("logs/auth-{$date}.log");

        if (!file_exists($logFilePath)) {
            $this->info("Aucun log trouvé pour la date {$date}");
            return 0;
        }

        $logContent = file_get_contents($logFilePath);

        if (empty($logContent)) {
            $this->info("Le fichier de log est vide pour la date {$date}");
            return 0;
        }

        Mail::raw($logContent, function ($message) use ($date) {
            $message->to('admin@neocap.net')
                ->subject("Logs du {$date}");
        });

        $this->info("Email envoyé avec succès pour les logs du {$date}");
        return 0;
    }
}