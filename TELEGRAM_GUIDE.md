# 📱 Telegram Bot Guide

## Overview
The SMS application now includes comprehensive Telegram notifications for daily, monthly, and yearly sales reports.

## Features

### 🔔 Instant Sale Notifications
- Automatically sends a notification when a sale is completed
- Shows order details, customer info, payment method, and items

### 📊 Daily Summary (Scheduled at 9:00 PM)
- Total sales and orders for the day
- Average order value
- Payment method breakdown
- Top 5 selling products
- Low stock alerts
- Employee attendance summary

### 📈 Monthly Summary (Scheduled at end of month at 11:00 PM)
- Total monthly revenue and orders
- Average order value and daily sales
- Growth comparison with previous month
- Payment method breakdown with percentages
- Top 10 products of the month

### 📅 Yearly Summary (Scheduled on Dec 31st at 11:59 PM)
- Total yearly revenue and orders
- Month-by-month breakdown
- Best and worst performing months
- Growth comparison with previous year
- Payment method distribution
- Top 10 products of the year

## Setup

### 1. Configure Telegram Bot
In your `.env` file, set:
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_ADMIN_CHAT_ID=your_chat_id_here
```

### 2. Get Your Bot Token
1. Message [@BotFather](https://t.me/BotFather) on Telegram
2. Send `/newbot` and follow the instructions
3. Copy the token provided

### 3. Get Your Chat ID
Run this command and send a message to your bot:
```bash
php artisan telegram:getchatid
```

### 4. Test the Connection
```bash
php artisan telegram:test
```

## Manual Commands

### Send Daily Report
```bash
php artisan telegram:daily-summary
```

### Send Monthly Report
```bash
php artisan telegram:monthly-summary
```

### Send Yearly Report
```bash
php artisan telegram:yearly-summary
```

### Send Specific Report Type
```bash
# Send daily report
php artisan telegram:report daily

# Send monthly report
php artisan telegram:report monthly

# Send yearly report
php artisan telegram:report yearly

# Send all reports at once
php artisan telegram:report all
```

## Automated Scheduling

The reports are automatically scheduled:
- **Daily**: Every day at 9:00 PM
- **Monthly**: Last day of each month at 11:00 PM
- **Yearly**: December 31st at 11:59 PM

To enable automatic scheduling, make sure the Laravel scheduler is running:

### Windows (Laragon)
Add this to Windows Task Scheduler:
```
Program: C:\laragon\bin\php\php-8.x.x\php.exe
Arguments: C:\laragon\www\Sale Management System\sms-app\artisan schedule:run
Start in: C:\laragon\www\Sale Management System\sms-app
Trigger: Every 1 minute
```

### Linux/Mac (Cron)
Add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Report Contents

### Daily Report Includes:
- 💰 Total sales and orders
- 📊 Average order value
- 💳 Payment method breakdown
- 🏆 Top 5 selling products
- ⚠️ Low stock alerts (if any)
- 👥 Employee attendance (clocked in/out/still working)

### Monthly Report Includes:
- 💵 Total monthly revenue
- 📈 Growth percentage vs last month
- 📅 Average daily sales
- 💳 Payment methods with percentages
- 🏆 Top 10 products with quantities and revenue

### Yearly Report Includes:
- 💵 Total yearly revenue
- 📈 Growth percentage vs last year
- 📅 Month-by-month breakdown
- 🏆 Best and worst performing months
- 💳 Payment method distribution
- 🏆 Top 10 products of the year with stats

## Troubleshooting

### Bot not sending messages?
1. Check `.env` configuration
2. Clear config cache: `php artisan config:clear`
3. Test connection: `php artisan telegram:test`
4. Make sure you started a conversation with your bot first

### Scheduler not working?
1. Verify Laravel scheduler is running (see Automated Scheduling above)
2. Check logs: `storage/logs/laravel.log`
3. Test manually: `php artisan schedule:run`

### No data in reports?
- Make sure you have sales in the database
- Check date ranges in the reports
- Verify database connection in `.env`

## Support
For issues or questions, check the application logs in `storage/logs/laravel.log`
