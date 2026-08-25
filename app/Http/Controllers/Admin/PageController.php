<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a listing of CMS pages.
     */
    public function index(): View
    {
        $pages = Page::latest()->paginate(15);

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new CMS page.
     */
    public function create(): View
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created CMS page in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $request->file('og_image')->store('pages', 'public');
        }

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully!');
    }

    /**
     * Show the form for editing the specified CMS page.
     */
    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified CMS page in storage.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('og_image')) {
            if ($page->og_image && Storage::disk('public')->exists($page->og_image)) {
                Storage::disk('public')->delete($page->og_image);
            }
            $validated['og_image'] = $request->file('og_image')->store('pages', 'public');
        }

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully!');
    }

    /**
     * Remove the specified CMS page from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        if ($page->og_image && Storage::disk('public')->exists($page->og_image)) {
            Storage::disk('public')->delete($page->og_image);
        }

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully!');
    }
}
