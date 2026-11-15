<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary to Telegram at 9 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending daily summary to Telegram...');
        
        $telegramService = new TelegramNotificationService();
        $telegramService->sendDailySummary();
        
        $this->info('Daily summary sent successfully!');
        
        return Command::SUCCESS;
    }
}
