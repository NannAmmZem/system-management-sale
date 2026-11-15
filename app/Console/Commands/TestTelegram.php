<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramNotificationService;

class TestTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram bot connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Telegram bot...');
        $this->info('');
        
        $telegramService = new TelegramNotificationService();
        $result = $telegramService->sendTestNotification();
        
        if ($result['success']) {
            $this->info('✅ SUCCESS! Check your Telegram bot for the test message!');
        } else {
            $this->error('❌ FAILED: ' . $result['message']);
            $this->info('');
            $this->info('Troubleshooting:');
            $this->info('1. Check your .env file has correct TELEGRAM_BOT_TOKEN and TELEGRAM_ADMIN_CHAT_ID');
            $this->info('2. Make sure you sent a message to your bot first');
            $this->info('3. Run: php artisan config:clear');
        }
        
        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
