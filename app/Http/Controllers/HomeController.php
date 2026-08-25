<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the customer storefront homepage.
     */
    public function index(): View
    {
        $heroTitle = Setting::get('hero_title', 'Delicious Food Delivered To Your Door');
        $heroSubtitle = Setting::get('hero_subtitle', 'Handcrafted with passion using fresh, authentic ingredients');
        $heroCtaText = Setting::get('hero_cta_text', 'Browse Menu');
        $heroCtaLink = Setting::get('hero_cta_link', route('menu'));

        try {
            $featuredCategories = Category::active()
                ->ordered()
                ->take(6)
                ->get();

            $featuredProducts = Product::available()
                ->whereHas('category', fn ($q) => $q->active())
                ->with(['category', 'variants' => fn ($q) => $q->active()])
                ->take(6)
                ->get();
        } catch (\Throwable $e) {
            $featuredCategories = collect();
            $featuredProducts = collect();
        }

        return view('frontend.home', compact(
            'heroTitle',
            'heroSubtitle',
            'heroCtaText',
            'heroCtaLink',
            'featuredCategories',
            'featuredProducts'
        ));
    }
}
