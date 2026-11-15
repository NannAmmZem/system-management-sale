<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;

class SendYearlySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:yearly-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send yearly summary to Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending yearly summary to Telegram...');
        
        $telegramService = new TelegramNotificationService();
        $telegramService->sendYearlySummary();
        
        $this->info('Yearly summary sent successfully!');
        
        return Command::SUCCESS;
    }
}
