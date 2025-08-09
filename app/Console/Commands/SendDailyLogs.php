<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyLogs extends Command
{
    protected $signature = 'logs:send-daily';
    protected $description = 'Send daily mail';

    public function handle()
    {
        $date = Carbon::yesterday()->format('Y-m-d');
        $logFilePath = storage_path("logs/laravel-{$date}.log");

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