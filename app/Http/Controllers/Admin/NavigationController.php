<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavigationController extends Controller
{
    /**
     * Display navigation menus and their associated items.
     */
    public function index(): View
    {
        $menus = NavigationMenu::with(['items' => fn ($q) => $q->orderBy('order'), 'items.page'])->get();
        $pages = Page::published()->orderBy('title')->get();

        return view('admin.navigation.index', compact('menus', 'pages'));
    }

    /**
     * Create a new navigation menu container.
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255|unique:navigation_menus,location',
        ]);

        NavigationMenu::create($validated);

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation menu created successfully!');
    }

    /**
     * Store a new navigation item in a menu.
     */
    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'navigation_menu_id' => 'required|exists:navigation_menus,id',
            'label' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'page_id' => 'nullable|exists:pages,id',
            'order' => 'nullable|integer|min:0',
            'target' => 'nullable|in:_self,_blank',
        ]);

        if (! isset($validated['order'])) {
            $maxOrder = NavigationItem::where('navigation_menu_id', $validated['navigation_menu_id'])->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }

        $validated['target'] = $validated['target'] ?? '_self';

        NavigationItem::create($validated);

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation item added successfully!');
    }

    /**
     * Update an existing navigation item.
     */
    public function updateItem(Request $request, NavigationItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'page_id' => 'nullable|exists:pages,id',
            'order' => 'required|integer|min:0',
            'target' => 'required|in:_self,_blank',
        ]);

        $item->update($validated);

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation item updated successfully!');
    }

    /**
     * Remove a navigation item.
     */
    public function destroyItem(NavigationItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation item removed successfully!');
    }

    /**
     * Reorder navigation items.
     */
    public function reorder(Request $request): JsonResponse|RedirectResponse
    {
        $items = $request->input('items', []);

        foreach ($items as $itemData) {
            if (isset($itemData['id'], $itemData['order'])) {
                NavigationItem::where('id', $itemData['id'])->update(['order' => (int) $itemData['order']]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation items reordered!');
    }
}
