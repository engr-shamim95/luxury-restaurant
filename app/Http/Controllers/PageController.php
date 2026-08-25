<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    /**
     * Display the specified dynamic CMS page.
     * Checks for a dedicated template (e.g. frontend.page-about-us) first,
     * then falls back to the generic frontend.page template.
     */
    public function show(string $slug): View
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $dedicatedView = 'frontend.page-' . $slug;

        if (view()->exists($dedicatedView)) {
            return view($dedicatedView, compact('page'));
        }

        return view('frontend.page', compact('page'));
    }
}
