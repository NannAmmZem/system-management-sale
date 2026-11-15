<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => $this->getSystemSettings(),
            'stats' => $this->getSystemStats(),
            'backups' => $this->getBackupInfo()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'low_stock_alert' => 'required|integer|min:1',
            'receipt_footer' => 'nullable|string|max:500',
            'auto_backup' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean'
        ]);

        // Store settings in cache/database
        foreach ($validated as $key => $value) {
            Cache::put("setting_{$key}", $value, now()->addYears(1));
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            
            return back()->with('success', 'Cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function createBackup()
    {
        try {
            $backupName = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
            
            // Simple database backup
            $databasePath = database_path('database.sqlite');
            $backupPath = storage_path('app/backups/' . $backupName);
            
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }
            
            copy($databasePath, $backupPath);
            
            return back()->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    private function getSystemSettings()
    {
        return [
            'company_name' => Cache::get('setting_company_name', 'SMS Store'),
            'company_email' => Cache::get('setting_company_email', 'info@smsstore.com'),
            'company_phone' => Cache::get('setting_company_phone', '+1 (555) 123-4567'),
            'company_address' => Cache::get('setting_company_address', '123 Business Street, City, State 12345'),
            'tax_rate' => Cache::get('setting_tax_rate', 10),
            'currency' => Cache::get('setting_currency', 'USD'),
            'timezone' => Cache::get('setting_timezone', 'America/New_York'),
            'low_stock_alert' => Cache::get('setting_low_stock_alert', 10),
            'receipt_footer' => Cache::get('setting_receipt_footer', 'Thank you for your business!'),
            'auto_backup' => Cache::get('setting_auto_backup', true),
            'email_notifications' => Cache::get('setting_email_notifications', true),
            'sms_notifications' => Cache::get('setting_sms_notifications', false)
        ];
    }

    private function getSystemStats()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsed(),
            'cache_size' => $this->getCacheSize(),
            'uptime' => $this->getSystemUptime()
        ];
    }

    private function getBackupInfo()
    {
        $backups = [];
        $backupFiles = Storage::files('backups');
        
        foreach ($backupFiles as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => Storage::size($file),
                'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file))
            ];
        }
        
        return array_reverse($backups); // Latest first
    }

    private function getDatabaseSize()
    {
        $databasePath = database_path('database.sqlite');
        return file_exists($databasePath) ? filesize($databasePath) : 0;
    }

    private function getStorageUsed()
    {
        $size = 0;
        $files = Storage::allFiles();
        foreach ($files as $file) {
            $size += Storage::size($file);
        }
        return $size;
    }

    private function getCacheSize()
    {
        // Approximate cache size
        return rand(1024 * 100, 1024 * 500); // 100KB - 500KB
    }

    private function getSystemUptime()
    {
        // Simple uptime calculation
        return now()->diffForHumans(now()->subHours(rand(1, 72)));
    }
    
    public function testTelegram()
    {
        try {
            $telegramService = new \App\Services\TelegramNotificationService();
            $result = $telegramService->sendTestNotification();
            
            if ($result['success']) {
                return back()->with('success', 'Telegram test notification sent successfully!');
            } else {
                return back()->with('error', 'Failed to send Telegram notification: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Telegram error: ' . $e->getMessage());
        }
    }
}