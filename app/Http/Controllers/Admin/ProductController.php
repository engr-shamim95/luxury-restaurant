<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filtering.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'variants']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product and its initial variants.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_available' => 'nullable|boolean',
            'has_variants' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.type' => 'nullable|string|max:50',
            'variants.*.price_adjustment' => 'required_with:variants|numeric',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_available'] = $request->boolean('is_available', true);
        $variants = $request->input('variants', []);
        $hasVariants = ! empty($variants) || $request->boolean('has_variants', false);
        $validated['has_variants'] = $hasVariants;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        if (! empty($variants)) {
            foreach ($variants as $variantData) {
                if (! empty($variantData['name'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $variantData['name'],
                        'type' => $variantData['type'] ?? 'size',
                        'price_adjustment' => (float) ($variantData['price_adjustment'] ?? 0),
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load('variants');
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product and its variants in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'is_available' => 'nullable|boolean',
            'has_variants' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.type' => 'nullable|string|max:50',
            'variants.*.price_adjustment' => 'required_with:variants|numeric',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $variants = $request->input('variants', []);
        $validated['has_variants'] = ! empty($variants) || $product->variants()->count() > 0;

        $product->update($validated);

        if (! empty($variants)) {
            foreach ($variants as $vData) {
                if (empty($vData['name'])) {
                    continue;
                }

                if (! empty($vData['id'])) {
                    ProductVariant::where('id', $vData['id'])
                        ->where('product_id', $product->id)
                        ->update([
                            'name' => $vData['name'],
                            'type' => $vData['type'] ?? 'size',
                            'price_adjustment' => (float) ($vData['price_adjustment'] ?? 0),
                        ]);
                } else {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vData['name'],
                        'type' => $vData['type'] ?? 'size',
                        'price_adjustment' => (float) ($vData['price_adjustment'] ?? 0),
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product and its image from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Store a standalone variant for an existing product.
     */
    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'price_adjustment' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['product_id'] = $product->id;
        $validated['type'] = $validated['type'] ?? 'size';
        $validated['is_active'] = $request->boolean('is_active', true);

        ProductVariant::create($validated);
        $product->update(['has_variants' => true]);

        return back()->with('success', 'Product variant added successfully!');
    }

    /**
     * Update an existing product variant.
     */
    public function updateVariant(Request $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'price_adjustment' => 'required|numeric',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['type'] = $validated['type'] ?? $variant->type ?? 'size';
        $validated['is_active'] = $request->boolean('is_active', true);

        $variant->update($validated);

        return back()->with('success', 'Product variant updated successfully!');
    }

    /**
     * Remove a product variant.
     */
    public function destroyVariant(ProductVariant $variant): RedirectResponse
    {
        $product = $variant->product;
        $variant->delete();

        if ($product && $product->variants()->count() === 0) {
            $product->update(['has_variants' => false]);
        }

        return back()->with('success', 'Product variant removed successfully!');
    }
}
