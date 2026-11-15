<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;

class SendMonthlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:monthly-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly summary to Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending monthly summary to Telegram...');
        
        $telegramService = new TelegramNotificationService();
        $telegramService->sendMonthlySummary();
        
        $this->info('Monthly summary sent successfully!');
        
        return Command::SUCCESS;
    }
}
