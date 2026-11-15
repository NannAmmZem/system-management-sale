<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        return Inertia::render('Pos/Index', [
            'products' => Product::with('category')
                ->where('qty', '>', 0)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name_en ?? $product->name,
                        'price' => (float) $product->sale_price,
                        'cost' => (float) $product->cost_price,
                        'qty' => $product->qty,
                        'barcode' => $product->barcode,
                        'category_id' => $product->category_id,
                        'category' => $product->category,
                        'image_url' => $product->image_url,
                        'description' => $product->description,
                        'status' => $product->status
                    ];
                }),
            'categories' => Category::all(),
            'customers' => Customer::all(),
            'recent_sales' => Sale::with('saleItems')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'order_number' => $sale->order_number,
                        'customer_name' => $sale->customer_name,
                        'total' => (float) $sale->total,
                        'payment_method' => $sale->payment_method,
                        'created_at' => $sale->created_at->format('M d, Y H:i'),
                        'items_count' => $sale->saleItems->count()
                    ];
                })
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,mobile_payment,bank_transfer',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = collect($validated['cart'])->sum(function ($item) {
                return $item['price'] * $item['quantity'];
            });

            $discount = $validated['discount_amount'] ?? 0;
            $taxRate = $validated['tax_rate'] ?? 10; // Default 10%
            $tax = ($subtotal - $discount) * ($taxRate / 100);
            $total = $subtotal - $discount + $tax;

            // Generate order number
            $orderNumber = 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            // Create sale
            $sale = Sale::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'] ?? 'Walk-in Customer',
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => 0,
                'total' => $total,
                'total_price' => $total, // For backward compatibility
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null
            ]);

            // Create sale items and update inventory
            foreach ($validated['cart'] as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => Product::find($item['id'])->name_en ?? Product::find($item['id'])->name,
                    'quantity' => $item['quantity'],
                    'qty' => $item['quantity'], // For backward compatibility
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ]);

                // Update product quantity
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrement('qty', $item['quantity']);
                }
            }

            DB::commit();

            // Send Telegram notification
            try {
                $telegramService = new \App\Services\TelegramNotificationService();
                $telegramService->notifyNewSale($sale);
            } catch (\Exception $e) {
                \Log::error('Failed to send Telegram notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'sale' => [
                    'id' => $sale->id,
                    'order_number' => $sale->order_number,
                    'total' => (float) $sale->total,
                    'customer_name' => $sale->customer_name,
                    'payment_method' => ucfirst(str_replace('_', ' ', $sale->payment_method)),
                    'created_at' => $sale->created_at->format('M d, Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('query');
        
        $products = Product::where('qty', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name_en', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
}