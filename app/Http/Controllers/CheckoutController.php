<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CheckoutController extends Controller
{
    public function index()
    {
        return Inertia::render('Checkout');
    }

    public function process(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,mobile_payment,bank_transfer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'shipping' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique order number and invoice number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $invoiceNumber = Sale::generateInvoiceNumber();

            // Create sale record
            $sale = Sale::create([
                'order_number' => $orderNumber,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'],
                'shipping' => $validated['shipping'],
                'total' => $validated['total'],
                'total_price' => $validated['total'], // Add for backward compatibility
                'status' => 'completed',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30), // 30 days payment terms
                'user_id' => auth()->id(), // null if guest
            ]);

            // Create sale items and update product quantities
            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'qty' => $item['quantity'], // For backward compatibility
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ]);

                // Update product quantity
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('qty', $item['quantity']);
                }
            }

            // Load sale items for receipt
            $sale->load('saleItems');

            // Generate PDF receipt and invoice
            $receiptPath = $this->generateReceipt($sale);
            $invoicePath = $this->generateInvoice($sale);

            DB::commit();

            // Send notifications (don't let email failures break the checkout)
            try {
                $this->sendCustomerEmail($sale, $receiptPath, $invoicePath);
                $this->sendAdminNotification($sale);
                
                // Send Telegram notification
                $telegramService = new \App\Services\TelegramNotificationService();
                $telegramService->notifyNewSale($sale);
            } catch (\Exception $e) {
                \Log::error('Failed to send notifications: ' . $e->getMessage());
            }

            // Return success response with Inertia
            return Inertia::render('Checkout', [
                'flash' => [
                    'success' => true,
                    'message' => 'Order placed successfully!',
                    'order' => [
                        'id' => $sale->id,
                        'order_number' => $sale->order_number,
                        'customer_name' => $sale->customer_name,
                        'customer_email' => $sale->customer_email,
                        'customer_phone' => $sale->customer_phone,
                        'shipping_address' => $sale->shipping_address,
                        'city' => $sale->city,
                        'payment_method' => ucfirst(str_replace('_', ' ', $sale->payment_method)),
                        'total' => (float) $sale->total,
                        'status' => $sale->status,
                        'created_at' => $sale->created_at->format('M d, Y H:i'),
                        'items' => $sale->saleItems->map(function($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->quantity * $item->price
                            ];
                        }),
                        'receipt_url' => $receiptPath ? Storage::url($receiptPath) : null,
                        'receipt_path' => $receiptPath,
                        'invoice_url' => $invoicePath ? Storage::url($invoicePath) : null,
                        'invoice_path' => $invoicePath,
                        'invoice_number' => $sale->invoice_number
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Checkout failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'order_data' => $validated
            ]);
            
            return Inertia::render('Checkout', [
                'errors' => [
                    'checkout' => 'Failed to process order: ' . $e->getMessage()
                ]
            ]);
        }
    }

    public function show($id)
    {
        $sale = Sale::with('saleItems')->findOrFail($id);
        
        return Inertia::render('Orders/Receipt', [
            'order' => $sale
        ]);
    }

    public function downloadInvoice($id)
    {
        \Log::info("Download invoice requested for order ID: $id");
        
        $sale = Sale::with('saleItems')->findOrFail($id);
        \Log::info("Sale found: " . $sale->order_number . " with invoice: " . $sale->invoice_number);
        
        // Generate invoice path
        $invoicePath = 'invoices/invoice-' . $sale->invoice_number . '.pdf';
        \Log::info("Looking for invoice at path: " . $invoicePath);
        \Log::info("Full system path: " . Storage::disk('local')->path($invoicePath));
        
        // Check if file exists
        if (Storage::disk('local')->exists($invoicePath)) {
            \Log::info("Invoice file exists, starting download");
            return Storage::disk('local')->download($invoicePath, "Invoice-{$sale->invoice_number}.pdf");
        }
        
        // If file doesn't exist, try to generate it
        \Log::warning("Invoice file not found, attempting to generate new one");
        $generatedPath = $this->generateInvoice($sale);
        
        if ($generatedPath && Storage::disk('local')->exists($generatedPath)) {
            \Log::info("New invoice generated successfully, starting download");
            return Storage::disk('local')->download($generatedPath, "Invoice-{$sale->invoice_number}.pdf");
        }
        
        \Log::error("Failed to find or generate invoice file");
        abort(404, 'Invoice not found');
    }

    public function downloadReceipt($id)
    {
        \Log::info("Download receipt requested for order ID: $id");
        
        $sale = Sale::with('saleItems')->findOrFail($id);
        \Log::info("Sale found: " . $sale->order_number);
        
        // Generate receipt path
        $receiptPath = 'receipts/receipt-' . $sale->order_number . '.pdf';
        \Log::info("Looking for receipt at path: " . $receiptPath);
        \Log::info("Full system path: " . Storage::disk('local')->path($receiptPath));
        
        // Check if file exists
        if (Storage::disk('local')->exists($receiptPath)) {
            \Log::info("Receipt file exists, starting download");
            return Storage::disk('local')->download($receiptPath, "Receipt-{$sale->order_number}.pdf");
        }
        
        // If file doesn't exist, try to generate it
        \Log::warning("Receipt file not found, attempting to generate new one");
        $generatedPath = $this->generateReceipt($sale);
        
        if ($generatedPath && Storage::disk('local')->exists($generatedPath)) {
            \Log::info("New receipt generated successfully, starting download");
            return Storage::disk('local')->download($generatedPath, "Receipt-{$sale->order_number}.pdf");
        }
        
        \Log::error("Failed to find or generate receipt file");
        abort(404, 'Receipt not found');
    }

    protected function generateInvoice($sale)
    {
        try {
            \Log::info('Starting invoice generation for order: ' . $sale->order_number);
            
            $data = [
                'order' => $sale,
                'items' => $sale->saleItems,
                'company' => [
                    'name' => 'SMS Store',
                    'address' => '123 Business Street, City, State 12345',
                    'phone' => '+1 (555) 123-4567',
                    'email' => 'info@smsstore.com'
                ]
            ];

            $pdf = Pdf::loadView('invoices.invoice', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'invoices/invoice-' . $sale->invoice_number . '.pdf';
            
            // Ensure invoices directory exists
            if (!Storage::disk('local')->exists('invoices')) {
                Storage::disk('local')->makeDirectory('invoices');
            }
            
            Storage::disk('local')->put($filename, $pdf->output());
            
            \Log::info('Invoice generated successfully: ' . $filename);
            \Log::info('Full path: ' . Storage::disk('local')->path($filename));
            return $filename;
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice: ' . $e->getMessage(), [
                'order_number' => $sale->order_number,
                'invoice_number' => $sale->invoice_number,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function generateReceipt($sale)
    {
        try {
            \Log::info('Starting receipt generation for order: ' . $sale->order_number);
            
            $data = [
                'order' => $sale,
                'items' => $sale->saleItems,
                'company' => [
                    'name' => 'SMS Store',
                    'address' => '123 Business Street, City, State 12345',
                    'phone' => '+1 (555) 123-4567',
                    'email' => 'info@smsstore.com'
                ]
            ];

            $pdf = Pdf::loadView('receipts.order', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'receipts/receipt-' . $sale->order_number . '.pdf';
            
            // Ensure receipts directory exists
            if (!Storage::disk('local')->exists('receipts')) {
                Storage::disk('local')->makeDirectory('receipts');
            }
            
            Storage::disk('local')->put($filename, $pdf->output());
            
            \Log::info('Receipt generated successfully: ' . $filename);
            \Log::info('Full path: ' . Storage::disk('local')->path($filename));
            return $filename;
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate receipt: ' . $e->getMessage(), [
                'order_number' => $sale->order_number,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function sendCustomerEmail($sale, $receiptPath = null, $invoicePath = null)
    {
        try {
            $data = [
                'order' => $sale,
                'items' => $sale->saleItems,
            ];

            Mail::send('emails.order-confirmation', $data, function ($message) use ($sale, $receiptPath, $invoicePath) {
                $message->to($sale->customer_email, $sale->customer_name)
                    ->subject('🎉 Order Confirmation & Invoice - ' . $sale->order_number . ' - SMS Store');
                
                // Attach receipt
                if ($receiptPath && Storage::disk('local')->exists($receiptPath)) {
                    $message->attach(Storage::disk('local')->path($receiptPath), [
                        'as' => "Receipt-{$sale->order_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
                }
                
                // Attach invoice
                if ($invoicePath && Storage::disk('local')->exists($invoicePath)) {
                    $message->attach(Storage::disk('local')->path($invoicePath), [
                        'as' => "Invoice-{$sale->invoice_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
                }
            });
            
            \Log::info("Customer email sent successfully to: {$sale->customer_email}");
            
        } catch (\Exception $e) {
            \Log::error('Failed to send customer email: ' . $e->getMessage());
        }
    }

    protected function sendAdminNotification($sale)
    {
        try {
            // Get admin users
            $admins = User::role('admin')->get();
            
            if ($admins->isEmpty()) {
                // Fallback: get all users if no admin role exists
                $admins = User::all();
                \Log::warning('No admin users found, sending to all users');
            }

            foreach ($admins as $admin) {
                $data = [
                    'order' => $sale,
                    'items' => $sale->saleItems,
                    'admin_name' => $admin->name
                ];

                Mail::send('emails.new-order-admin', $data, function ($message) use ($sale, $admin) {
                    $message->to($admin->email, $admin->name)
                        ->subject('🎉 New Order Alert - ' . $sale->order_number . ' - SMS Store');
                });
                
                \Log::info("Admin notification sent to: {$admin->email}");
            }

            // Also trigger real-time notification for dashboard
            $this->triggerRealTimeNotification($sale);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    protected function triggerRealTimeNotification($sale)
    {
        // This would typically use WebSockets or Server-Sent Events
        // For now, we'll use a simple cache-based approach that the dashboard can poll
        try {
            $notification = [
                'type' => 'new_order',
                'title' => 'New Order Received! 🎉',
                'message' => "Order #{$sale->order_number} - $" . number_format($sale->total, 2),
                'order_id' => $sale->id,
                'order_number' => $sale->order_number,
                'customer_name' => $sale->customer_name,
                'total' => $sale->total,
                'timestamp' => now()->toISOString()
            ];

            // Store in cache for dashboard to pick up
            $existingNotifications = cache()->get('admin_notifications', []);
            array_unshift($existingNotifications, $notification);
            
            // Keep only last 50 notifications
            $existingNotifications = array_slice($existingNotifications, 0, 50);
            
            cache()->put('admin_notifications', $existingNotifications, now()->addHours(24));
            
        } catch (\Exception $e) {
            \Log::error('Failed to trigger real-time notification: ' . $e->getMessage());
        }
    }
}
