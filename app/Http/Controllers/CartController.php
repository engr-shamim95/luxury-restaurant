<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the shopping cart items and calculated totals.
     */
    public function index(): View
    {
        $cart = session('cart', []);
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float) $item['price'] * (int) $item['quantity']);
        }

        $taxRate = (float) Setting::get('tax_rate', 0);
        $tax = $taxRate > 1 ? round($subtotal * ($taxRate / 100), 2) : round($subtotal * $taxRate, 2);
        $deliveryFee = (float) Setting::get('delivery_fee', 0);
        $total = round($subtotal + $tax, 2);

        return view('frontend.cart', compact('cart', 'subtotal', 'taxRate', 'tax', 'deliveryFee', 'total'));
    }

    /**
     * Add an item (simple or with variant) to the session cart.
     */
    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (! $product->is_available) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Product is currently unavailable.'], 422);
            }
            return redirect()->back()->with('error', 'Product is currently unavailable.');
        }

        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->where('id', $request->variant_id)
                ->where('is_active', true)
                ->first();
        }

        $unitPrice = (float) $product->base_price + ($variant ? (float) $variant->price_adjustment : 0.0);
        $itemKey = $variant ? "item_{$product->id}_var_{$variant->id}" : "item_{$product->id}_simple";
        $quantity = (int) $request->quantity;

        $cart = session('cart', []);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['quantity'] += $quantity;
            $cart[$itemKey]['subtotal'] = round($cart[$itemKey]['price'] * $cart[$itemKey]['quantity'], 2);
        } else {
            $variantDisplay = null;
            if ($variant) {
                $adj = (float) $variant->price_adjustment;
                $symbol = Setting::get('currency_symbol', '$');
                $variantDisplay = $variant->name;
                if ($adj != 0) {
                    $variantDisplay .= ' (' . ($adj > 0 ? '+ ' : '- ') . $symbol . number_format(abs($adj), 2) . ')';
                }
            }

            $cart[$itemKey] = [
                'item_key' => $itemKey,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_id' => $variant?->id,
                'variant_name' => $variantDisplay,
                'price' => $unitPrice,
                'quantity' => $quantity,
                'image' => $product->image,
                'subtotal' => round($unitPrice * $quantity, 2),
            ];
        }

        session(['cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart!',
                'cart' => $cart,
                'cart_count' => array_sum(array_column($cart, 'quantity')),
            ]);
        }

        return redirect()->back()->with('success', 'Item added to cart!');
    }

    /**
     * Update quantity of an item in the session cart.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'item_key' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $itemKey = $request->input('item_key');
        $quantity = (int) $request->input('quantity');
        $cart = session('cart', []);

        if (isset($cart[$itemKey])) {
            if ($quantity <= 0) {
                unset($cart[$itemKey]);
            } else {
                $cart[$itemKey]['quantity'] = $quantity;
                $cart[$itemKey]['subtotal'] = round($cart[$itemKey]['price'] * $quantity, 2);
            }
            session(['cart' => $cart]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'cart' => $cart,
                'cart_count' => array_sum(array_column($cart, 'quantity')),
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the session cart.
     */
    public function remove(Request $request, ?string $itemKey = null): RedirectResponse|JsonResponse
    {
        $key = $itemKey ?? $request->input('item_key');
        $cart = session('cart', []);

        if ($key && isset($cart[$key])) {
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart' => $cart,
                'cart_count' => array_sum(array_column($cart, 'quantity')),
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear all items from the session cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        session()->forget('cart');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart' => [],
                'cart_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Cart cleared.');
    }
}
