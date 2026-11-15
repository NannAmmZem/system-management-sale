<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['category', 'supplier'])->latest()->get(),
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
            'can' => [
                'create' => auth()->user()->can('create products'),
                'edit' => auth()->user()->can('edit products'),
                'delete' => auth()->user()->can('delete products'),
            ],
        ]);
    }

    public function show($id)
    {
        // Find product or fail with 404
        $product = Product::with(['category', 'supplier'])->findOrFail($id);
        
        // Get related products from the same category
        $relatedProducts = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->where('qty', '>', 0)
            ->take(4)
            ->get();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['category', 'supplier'])->latest()->get(),
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log what we're receiving
        \Log::info('Product store request', [
            'has_file' => $request->hasFile('image'),
            'all_data' => $request->all(),
            'files' => $request->allFiles()
        ]);

        $request->validate([
            'name_en' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'alert_qty' => 'nullable|integer|min:0',
            'barcode' => 'required|string|unique:products,barcode',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        $data = $request->all();
        
        // Set default values
        $data['status'] = $data['status'] ?? 'active';
        $data['alert_qty'] = $data['alert_qty'] ?? 10;

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $imageName, 'public');
            $data['image'] = $path;
            
            \Log::info('Image uploaded successfully', [
                'path' => $path,
                'full_url' => asset('/storage/' . $path)
            ]);
        } else {
            \Log::info('No image file received');
        }

        $product = Product::create($data);
        
        \Log::info('Product created', [
            'id' => $product->id,
            'image_path' => $product->image,
            'image_url' => $product->image_url
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['category', 'supplier'])->latest()->get(),
            'categories' => Category::all(),
            'suppliers' => Supplier::all(),
            'editProduct' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'alert_qty' => 'nullable|integer|min:0',
            'barcode' => 'required|string|unique:products,barcode,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);
        
        $data = $request->all();
        
        // Set default values
        $data['status'] = $data['status'] ?? 'active';
        $data['alert_qty'] = $data['alert_qty'] ?? 10;

        // Handle Image Update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $imageName, 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        try {
            // Check if product has related sale items
            $hasSaleItems = DB::table('sale_items')->where('product_id', $product->id)->exists();
            
            if ($hasSaleItems) {
                return redirect()->back()->with('error', 'Cannot delete product. It has been sold and is referenced in sales records.');
            }
            
            // Delete image from storage when product is deleted
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $product->delete();
            
            return redirect()->back()->with('success', 'Product deleted successfully!');
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Cannot delete product. It is referenced by other records.');
            }
            
            return redirect()->back()->with('error', 'Failed to delete product: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the product: ' . $e->getMessage());
        }
    }
}