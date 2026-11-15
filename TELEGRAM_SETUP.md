# Telegram Notifications Setup Guide

This guide will help you set up Telegram notifications for your SMS application.

## Features

### 1. Instant Sale Notifications
- Receive immediate notifications when a customer makes a purchase
- Includes order details, customer info, payment method, and items

### 2. Daily Summary (9 PM)
- Comprehensive daily report sent automatically at 9:00 PM
- Includes:
  - Total sales and orders
  - Average order value
  - Payment method breakdown
  - Top 5 selling products
  - Low stock alerts
  - Attendance summary

## Setup Instructions

### Step 1: Create a Telegram Bot

1. Open Telegram and search for `@BotFather`
2. Start a chat and send `/newbot`
3. Follow the instructions:
   - Choose a name for your bot (e.g., "Nann's Coffee Bot")
   - Choose a username (must end in 'bot', e.g., "nanns_coffee_bot")
4. BotFather will give you a **Bot Token** - save this!
   - Example: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`

### Step 2: Get Your Chat ID

1. Start a chat with your new bot (click the link BotFather provides)
2. Send any message to your bot (e.g., "Hello")
3. Open this URL in your browser (replace YOUR_BOT_TOKEN):
   ```
   https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
   ```
4. Look for `"chat":{"id":` in the response
5. Copy the number after `"id":` - this is your **Chat ID**
   - Example: `123456789`

### Step 3: Configure Your Application

1. Open your `.env` file
2. Find these lines:
   ```
   TELEGRAM_BOT_TOKEN=your_bot_token_here
   TELEGRAM_ADMIN_CHAT_ID=your_chat_id_here
   ```
3. Replace with your actual values:
   ```
   TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
   TELEGRAM_ADMIN_CHAT_ID=123456789
   ```
4. Save the file

### Step 4: Test the Setup

Run this command to test if everything is working:

```bash
php artisan tinker
```

Then in tinker:

```php
$telegram = new \App\Services\TelegramNotificationService();
$telegram->sendTestNotification();
```

You should receive a test message in Telegram!

### Step 5: Set Up the Scheduler

For the daily summary to work automatically, you need to set up Laravel's task scheduler.

#### On Windows (Laragon/XAMPP):

1. Open Task Scheduler (search in Windows)
2. Click "Create Basic Task"
3. Name: "Laravel Scheduler"
4. Trigger: Daily
5. Time: Any time (the scheduler will handle the 9 PM timing)
6. Action: Start a program
7. Program: `C:\laragon\bin\php\php-8.x.x\php.exe` (adjust path)
8. Arguments: `artisan schedule:run`
9. Start in: `C:\laragon\www\Sale Management System\sms-app`
10. Finish

#### On Linux/Mac:

Add this to your crontab (`crontab -e`):

```bash
* * * * * cd /path/to/sms-app && php artisan schedule:run >> /dev/null 2>&1
```

### Step 6: Manual Testing

You can manually trigger the daily summary anytime:

```bash
php artisan telegram:daily-summary
```

## Notification Examples

### Instant Sale Notification:
```
🛒 NEW SALE ALERT!

📋 Order: ORD-20260207-ABC123
👤 Customer: John Doe
💰 Total: $45.50
💳 Payment: Cash
📦 Items: 3
🕐 Time: Feb 07, 2026 14:30

Items:
• Cappuccino x2 - $10.00
• Croissant x1 - $5.50
• Latte x1 - $30.00
```

### Daily Summary (9 PM):
```
📊 DAILY SUMMARY - Feb 07, 2026

💰 SALES REPORT
━━━━━━━━━━━━━━━━
📈 Total Sales: $1,234.56
🛒 Total Orders: 45
📊 Avg Order: $27.43

💳 Payment Methods:
• Cash: 25 orders ($678.90)
• Card: 15 orders ($445.66)
• Mobile payment: 5 orders ($110.00)

🏆 Top Products:
🥇 Cappuccino: 35 sold ($350.00)
🥈 Latte: 28 sold ($560.00)
🥉 Croissant: 20 sold ($100.00)

⚠️ LOW STOCK ALERTS (3)
━━━━━━━━━━━━━━━━
• Coffee Beans: 5 left (Alert: 10)
• Milk: 8 left (Alert: 15)
• Sugar: 12 left (Alert: 20)

👥 ATTENDANCE
━━━━━━━━━━━━━━━━
✅ Clocked In: 8
🏁 Clocked Out: 6
🔄 Still Working: 2

━━━━━━━━━━━━━━━━
📱 Nann's Coffee SMS
🕐 Generated at: 21:00
```

## Troubleshooting

### Not receiving notifications?

1. **Check bot token and chat ID**:
   - Make sure they're correct in `.env`
   - Run `php artisan config:clear`

2. **Check if bot is blocked**:
   - Open Telegram and search for your bot
   - Make sure you haven't blocked it
   - Send it a message

3. **Check logs**:
   - Look in `storage/logs/laravel.log` for errors

4. **Test manually**:
   ```bash
   php artisan telegram:daily-summary
   ```

### Scheduler not running?

1. **Check if scheduler is set up** (see Step 5)
2. **Test manually**:
   ```bash
   php artisan schedule:run
   ```
3. **Check scheduler list**:
   ```bash
   php artisan schedule:list
   ```

## Support

If you need help, check the Laravel logs at `storage/logs/laravel.log` for detailed error messages.
