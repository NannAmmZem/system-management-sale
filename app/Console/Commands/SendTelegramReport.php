<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;

class SendTelegramReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:report {type=daily : Type of report (daily, monthly, yearly, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sales report to Telegram (daily, monthly, yearly, or all)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $telegramService = new TelegramNotificationService();
        
        $this->info("Sending {$type} report(s) to Telegram...");
        $this->newLine();
        
        switch (strtolower($type)) {
            case 'daily':
                $this->info('📊 Sending daily summary...');
                $telegramService->sendDailySummary();
                $this->info('✅ Daily summary sent!');
                break;
                
            case 'monthly':
                $this->info('📊 Sending monthly summary...');
                $telegramService->sendMonthlySummary();
                $this->info('✅ Monthly summary sent!');
                break;
                
            case 'yearly':
                $this->info('📊 Sending yearly summary...');
                $telegramService->sendYearlySummary();
                $this->info('✅ Yearly summary sent!');
                break;
                
            case 'all':
                $this->info('📊 Sending all reports...');
                $this->newLine();
                
                $this->info('1/3 Sending daily summary...');
                $telegramService->sendDailySummary();
                $this->info('✅ Daily summary sent!');
                $this->newLine();
                
                sleep(2); // Small delay between messages
                
                $this->info('2/3 Sending monthly summary...');
                $telegramService->sendMonthlySummary();
                $this->info('✅ Monthly summary sent!');
                $this->newLine();
                
                sleep(2); // Small delay between messages
                
                $this->info('3/3 Sending yearly summary...');
                $telegramService->sendYearlySummary();
                $this->info('✅ Yearly summary sent!');
                break;
                
            default:
                $this->error("Invalid report type: {$type}");
                $this->info('Valid types: daily, monthly, yearly, all');
                return Command::FAILURE;
        }
        
        $this->newLine();
        $this->info('🎉 All reports sent successfully!');
        $this->info('Check your Telegram bot for the messages.');
        
        return Command::SUCCESS;
    }
}
