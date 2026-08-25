<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display the customer menu browsing page with category filtering.
     */
    public function index(Request $request): View
    {
        $categories = Category::active()
            ->ordered()
            ->get();

        $categorySlug = $request->query('category');
        $selectedCategory = null;

        $productsQuery = Product::available()
            ->whereHas('category', fn ($q) => $q->active())
            ->with(['category', 'variants' => fn ($q) => $q->active()]);

        if (! empty($categorySlug)) {
            $selectedCategory = Category::active()->where('slug', $categorySlug)->first();
            if ($selectedCategory) {
                $productsQuery->where('category_id', $selectedCategory->id);
            } else {
                $productsQuery->whereRaw('1 = 0');
            }
        }

        $products = $productsQuery->get();

        return view('frontend.menu', compact('categories', 'products', 'selectedCategory', 'categorySlug'));
    }
}
