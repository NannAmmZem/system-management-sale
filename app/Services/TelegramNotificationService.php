<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected $chatId;
    protected $enabled;

    public function __construct()
    {
        $this->chatId = env('TELEGRAM_ADMIN_CHAT_ID');
        $this->enabled = !empty(env('TELEGRAM_BOT_TOKEN')) && !empty($this->chatId);
    }

    /**
     * Send instant notification when a sale is made
     */
    public function notifyNewSale(Sale $sale)
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $message = "🛒 *NEW SALE ALERT!*\n\n";
            $message .= "📋 Order: `{$sale->order_number}`\n";
            $message .= "👤 Customer: {$sale->customer_name}\n";
            $message .= "💰 Total: $" . number_format($sale->total, 2) . "\n";
            $message .= "💳 Payment: " . ucfirst(str_replace('_', ' ', $sale->payment_method)) . "\n";
            $message .= "📦 Items: {$sale->saleItems->count()}\n";
            $message .= "🕐 Time: " . $sale->created_at->format('M d, Y H:i') . "\n\n";
            
            // Add items
            $message .= "*Items:*\n";
            foreach ($sale->saleItems as $item) {
                $message .= "• {$item->product_name} x{$item->quantity} - $" . number_format($item->total, 2) . "\n";
            }

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            Log::info('Telegram notification sent for sale: ' . $sale->order_number);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification: ' . $e->getMessage());
        }
    }

    /**
     * Send daily summary at 9 PM
     */
    public function sendDailySummary()
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $today = Carbon::today();
            
            // Get today's sales
            $todaySales = Sale::whereDate('created_at', $today)->get();
            $totalSales = $todaySales->sum('total');
            $totalOrders = $todaySales->count();
            $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
            
            // Get payment method breakdown
            $paymentBreakdown = $todaySales->groupBy('payment_method')->map(function ($sales, $method) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total')
                ];
            });
            
            // Get low stock products
            $lowStockProducts = Product::whereRaw('qty <= alert_qty')
                ->where('status', 'active')
                ->get();
            
            // Get today's attendance
            $todayAttendance = Attendance::whereDate('date', $today)->get();
            $clockedIn = $todayAttendance->whereNotNull('clock_in')->count();
            $clockedOut = $todayAttendance->whereNotNull('clock_out')->count();
            $stillWorking = $todayAttendance->whereNotNull('clock_in')->whereNull('clock_out')->count();
            
            // Build message
            $message = "📊 *DAILY SUMMARY - " . $today->format('M d, Y') . "*\n\n";
            
            // Sales Summary
            $message .= "💰 *SALES REPORT*\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "📈 Total Sales: $" . number_format($totalSales, 2) . "\n";
            $message .= "🛒 Total Orders: {$totalOrders}\n";
            $message .= "📊 Avg Order: $" . number_format($avgOrderValue, 2) . "\n\n";
            
            // Payment Methods
            if ($paymentBreakdown->count() > 0) {
                $message .= "💳 *Payment Methods:*\n";
                foreach ($paymentBreakdown as $method => $data) {
                    $methodName = ucfirst(str_replace('_', ' ', $method));
                    $message .= "• {$methodName}: {$data['count']} orders ($" . number_format($data['total'], 2) . ")\n";
                }
                $message .= "\n";
            }
            
            // Top Selling Products
            $topProducts = \DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereDate('sales.created_at', $today)
                ->select('sale_items.product_name', \DB::raw('SUM(sale_items.quantity) as total_qty'), \DB::raw('SUM(sale_items.total) as total_revenue'))
                ->groupBy('sale_items.product_name')
                ->orderBy('total_qty', 'desc')
                ->limit(5)
                ->get();
            
            if ($topProducts->count() > 0) {
                $message .= "🏆 *Top Products:*\n";
                foreach ($topProducts as $index => $product) {
                    $emoji = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'][$index] ?? '•';
                    $message .= "{$emoji} {$product->product_name}: {$product->total_qty} sold ($" . number_format($product->total_revenue, 2) . ")\n";
                }
                $message .= "\n";
            }
            
            // Stock Alerts
            if ($lowStockProducts->count() > 0) {
                $message .= "⚠️ *LOW STOCK ALERTS* ({$lowStockProducts->count()})\n";
                $message .= "━━━━━━━━━━━━━━━━\n";
                foreach ($lowStockProducts->take(5) as $product) {
                    $message .= "• {$product->name_en}: {$product->qty} left (Alert: {$product->alert_qty})\n";
                }
                if ($lowStockProducts->count() > 5) {
                    $message .= "• ... and " . ($lowStockProducts->count() - 5) . " more\n";
                }
                $message .= "\n";
            }
            
            // Attendance Summary
            $message .= "👥 *ATTENDANCE*\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "✅ Clocked In: {$clockedIn}\n";
            $message .= "🏁 Clocked Out: {$clockedOut}\n";
            $message .= "🔄 Still Working: {$stillWorking}\n\n";
            
            // Footer
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "📱 Nann's Coffee SMS\n";
            $message .= "🕐 Generated at: " . now()->format('H:i') . "\n";

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            Log::info('Daily summary sent to Telegram');
        } catch (\Exception $e) {
            Log::error('Failed to send daily summary: ' . $e->getMessage());
        }
    }

    /**
     * Send monthly summary
     */
    public function sendMonthlySummary()
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $currentMonth = Carbon::now()->format('F Y');
            
            // Get month's sales
            $monthlySales = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get();
            $totalSales = $monthlySales->sum('total');
            $totalOrders = $monthlySales->count();
            $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
            
            // Daily average
            $daysInMonth = Carbon::now()->daysInMonth;
            $currentDay = Carbon::now()->day;
            $avgDailySales = $currentDay > 0 ? $totalSales / $currentDay : 0;
            
            // Get payment method breakdown
            $paymentBreakdown = $monthlySales->groupBy('payment_method')->map(function ($sales, $method) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total')
                ];
            });
            
            // Top selling products of the month
            $topProducts = \DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
                ->select('sale_items.product_name', \DB::raw('SUM(sale_items.quantity) as total_qty'), \DB::raw('SUM(sale_items.total) as total_revenue'))
                ->groupBy('sale_items.product_name')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();
            
            // Compare with last month
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $lastMonthSales = Sale::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total');
            $growthRate = $lastMonthSales > 0 ? (($totalSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;
            $growthEmoji = $growthRate >= 0 ? '📈' : '📉';
            
            // Build message
            $message = "📊 *MONTHLY SUMMARY - {$currentMonth}*\n\n";
            
            // Sales Summary
            $message .= "💰 *SALES REPORT*\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "💵 Total Revenue: $" . number_format($totalSales, 2) . "\n";
            $message .= "🛒 Total Orders: {$totalOrders}\n";
            $message .= "📊 Avg Order: $" . number_format($avgOrderValue, 2) . "\n";
            $message .= "📅 Avg Daily Sales: $" . number_format($avgDailySales, 2) . "\n";
            $message .= "{$growthEmoji} Growth: " . number_format($growthRate, 1) . "% vs last month\n\n";
            
            // Payment Methods
            if ($paymentBreakdown->count() > 0) {
                $message .= "💳 *Payment Methods:*\n";
                foreach ($paymentBreakdown as $method => $data) {
                    $methodName = ucfirst(str_replace('_', ' ', $method));
                    $percentage = ($data['total'] / $totalSales) * 100;
                    $message .= "• {$methodName}: {$data['count']} orders ($" . number_format($data['total'], 2) . " - " . number_format($percentage, 1) . "%)\n";
                }
                $message .= "\n";
            }
            
            // Top Selling Products
            if ($topProducts->count() > 0) {
                $message .= "🏆 *Top 10 Products:*\n";
                foreach ($topProducts as $index => $product) {
                    $emoji = ['🥇', '🥈', '🥉'][$index] ?? ($index + 1) . '.';
                    $message .= "{$emoji} {$product->product_name}\n";
                    $message .= "   Sold: {$product->total_qty} | Revenue: $" . number_format($product->total_revenue, 2) . "\n";
                }
                $message .= "\n";
            }
            
            // Footer
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "📱 Nann's Coffee SMS\n";
            $message .= "🕐 Generated at: " . now()->format('M d, Y H:i') . "\n";

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            Log::info('Monthly summary sent to Telegram');
        } catch (\Exception $e) {
            Log::error('Failed to send monthly summary: ' . $e->getMessage());
        }
    }

    /**
     * Send yearly summary
     */
    public function sendYearlySummary()
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear = Carbon::now()->endOfYear();
            $currentYear = Carbon::now()->format('Y');
            
            // Get year's sales
            $yearlySales = Sale::whereBetween('created_at', [$startOfYear, Carbon::now()])->get();
            $totalSales = $yearlySales->sum('total');
            $totalOrders = $yearlySales->count();
            $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
            
            // Monthly average
            $currentMonth = Carbon::now()->month;
            $avgMonthlySales = $currentMonth > 0 ? $totalSales / $currentMonth : 0;
            
            // Get sales by month
            $salesByMonth = Sale::whereBetween('created_at', [$startOfYear, Carbon::now()])
                ->selectRaw('MONTH(created_at) as month, SUM(total) as total, COUNT(*) as orders')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');
            
            // Get payment method breakdown
            $paymentBreakdown = $yearlySales->groupBy('payment_method')->map(function ($sales, $method) {
                return [
                    'count' => $sales->count(),
                    'total' => $sales->sum('total')
                ];
            });
            
            // Top selling products of the year
            $topProducts = \DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.created_at', [$startOfYear, Carbon::now()])
                ->select('sale_items.product_name', \DB::raw('SUM(sale_items.quantity) as total_qty'), \DB::raw('SUM(sale_items.total) as total_revenue'))
                ->groupBy('sale_items.product_name')
                ->orderBy('total_revenue', 'desc')
                ->limit(10)
                ->get();
            
            // Compare with last year
            $lastYearStart = Carbon::now()->subYear()->startOfYear();
            $lastYearEnd = Carbon::now()->subYear()->endOfYear();
            $lastYearSales = Sale::whereBetween('created_at', [$lastYearStart, $lastYearEnd])->sum('total');
            $growthRate = $lastYearSales > 0 ? (($totalSales - $lastYearSales) / $lastYearSales) * 100 : 0;
            $growthEmoji = $growthRate >= 0 ? '📈' : '📉';
            
            // Find best and worst month
            $bestMonth = $salesByMonth->sortByDesc('total')->first();
            $worstMonth = $salesByMonth->sortBy('total')->first();
            
            // Build message
            $message = "📊 *YEARLY SUMMARY - {$currentYear}*\n\n";
            
            // Sales Summary
            $message .= "💰 *SALES REPORT*\n";
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "💵 Total Revenue: $" . number_format($totalSales, 2) . "\n";
            $message .= "🛒 Total Orders: {$totalOrders}\n";
            $message .= "📊 Avg Order: $" . number_format($avgOrderValue, 2) . "\n";
            $message .= "📅 Avg Monthly Sales: $" . number_format($avgMonthlySales, 2) . "\n";
            $message .= "{$growthEmoji} Growth: " . number_format($growthRate, 1) . "% vs last year\n\n";
            
            // Best and Worst Months
            if ($bestMonth && $worstMonth) {
                $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $message .= "📅 *Monthly Performance:*\n";
                $message .= "🏆 Best: {$monthNames[$bestMonth->month]} ($" . number_format($bestMonth->total, 2) . ")\n";
                $message .= "📉 Lowest: {$monthNames[$worstMonth->month]} ($" . number_format($worstMonth->total, 2) . ")\n\n";
            }
            
            // Monthly breakdown
            $message .= "📈 *Month by Month:*\n";
            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($salesByMonth as $month => $data) {
                $message .= "• {$monthNames[$month]}: $" . number_format($data->total, 2) . " ({$data->orders} orders)\n";
            }
            $message .= "\n";
            
            // Payment Methods
            if ($paymentBreakdown->count() > 0) {
                $message .= "💳 *Payment Methods:*\n";
                foreach ($paymentBreakdown as $method => $data) {
                    $methodName = ucfirst(str_replace('_', ' ', $method));
                    $percentage = ($data['total'] / $totalSales) * 100;
                    $message .= "• {$methodName}: {$data['count']} orders ($" . number_format($data['total'], 2) . " - " . number_format($percentage, 1) . "%)\n";
                }
                $message .= "\n";
            }
            
            // Top Selling Products
            if ($topProducts->count() > 0) {
                $message .= "🏆 *Top 10 Products of {$currentYear}:*\n";
                foreach ($topProducts as $index => $product) {
                    $emoji = ['🥇', '🥈', '🥉'][$index] ?? ($index + 1) . '.';
                    $message .= "{$emoji} {$product->product_name}\n";
                    $message .= "   Sold: {$product->total_qty} | Revenue: $" . number_format($product->total_revenue, 2) . "\n";
                }
                $message .= "\n";
            }
            
            // Footer
            $message .= "━━━━━━━━━━━━━━━━\n";
            $message .= "📱 Nann's Coffee SMS\n";
            $message .= "🕐 Generated at: " . now()->format('M d, Y H:i') . "\n";

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            Log::info('Yearly summary sent to Telegram');
        } catch (\Exception $e) {
            Log::error('Failed to send yearly summary: ' . $e->getMessage());
        }
    }

    /**
     * Test notification
     */
    public function sendTestNotification()
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Telegram not configured'];
        }

        try {
            $message = "✅ *Test Notification*\n\n";
            $message .= "Your Telegram bot is working correctly!\n";
            $message .= "🕐 Time: " . now()->format('M d, Y H:i') . "\n";

            Telegram::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            return ['success' => true, 'message' => 'Test notification sent!'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
