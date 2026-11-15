<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class GetTelegramChatId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:get-chat-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get your Telegram Chat ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Getting recent messages from your bot...');
        $this->info('');
        
        try {
            $updates = Telegram::getUpdates();
            
            if (count($updates) === 0) {
                $this->warn('⚠️  No messages found!');
                $this->info('');
                $this->info('Please do the following:');
                $this->info('1. Open Telegram');
                $this->info('2. Search for your bot');
                $this->info('3. Send any message to your bot (e.g., "Hello")');
                $this->info('4. Run this command again');
                return Command::FAILURE;
            }
            
            $this->info('✅ Found messages! Here are the Chat IDs:');
            $this->info('');
            
            $chatIds = [];
            foreach ($updates as $update) {
                if (isset($update['message']['chat']['id'])) {
                    $chatId = $update['message']['chat']['id'];
                    $chatType = $update['message']['chat']['type'] ?? 'unknown';
                    $firstName = $update['message']['chat']['first_name'] ?? 'Unknown';
                    $username = $update['message']['chat']['username'] ?? 'No username';
                    
                    if (!in_array($chatId, $chatIds)) {
                        $chatIds[] = $chatId;
                        $this->info("Chat ID: {$chatId}");
                        $this->info("  Name: {$firstName}");
                        $this->info("  Username: @{$username}");
                        $this->info("  Type: {$chatType}");
                        $this->info('');
                    }
                }
            }
            
            if (count($chatIds) > 0) {
                $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->info('📝 Copy one of the Chat IDs above and update your .env file:');
                $this->info('');
                $this->info('TELEGRAM_ADMIN_CHAT_ID=' . $chatIds[0]);
                $this->info('');
                $this->info('Then run: php artisan config:clear');
                $this->info('Then test: php artisan telegram:test');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->info('');
            $this->info('Make sure your TELEGRAM_BOT_TOKEN is correct in .env');
            return Command::FAILURE;
        }
    }
}
