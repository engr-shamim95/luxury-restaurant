<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SystemCommandController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController as FrontendPageController;

// Customer Storefront Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');

// Session Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{itemKey?}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Checkout & Order Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/stripe-callback/{order}', [CheckoutController::class, 'stripeSuccess'])->name('checkout.stripe.success');
Route::get('/checkout/square-callback/{order}', [CheckoutController::class, 'squareSuccess'])->name('checkout.square.success');
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'paymentCancel'])->name('checkout.cancel');
Route::get('/order/confirmation/{order}', [CheckoutController::class, 'success'])->name('order.confirmation');

// Dynamic CMS Pages
Route::get('/page/{slug}', [FrontendPageController::class, 'show'])->name('page.show');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Routes protected by Auth and IsAdmin Middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Settings Management
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // 3. CMS Pages CRUD
    Route::resource('pages', PageController::class);

    // 4. Navigation Menus & Items
    Route::get('/navigation', [NavigationController::class, 'index'])->name('navigation.index');
    Route::post('/navigation/menus', [NavigationController::class, 'storeMenu'])->name('navigation.menus.store');
    Route::post('/navigation/items', [NavigationController::class, 'storeItem'])->name('navigation.items.store');
    Route::put('/navigation/items/{item}', [NavigationController::class, 'updateItem'])->name('navigation.items.update');
    Route::delete('/navigation/items/{item}', [NavigationController::class, 'destroyItem'])->name('navigation.items.destroy');
    Route::post('/navigation/reorder', [NavigationController::class, 'reorder'])->name('navigation.reorder');

    // 5. Categories CRUD
    Route::resource('categories', CategoryController::class);

    // 6. Products CRUD & Variants Management
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::put('/variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
    Route::delete('/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');

    // 7. Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/print', [OrderController::class, 'printReceipt'])->name('orders.print');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // 8. Zero-Terminal System Manager
    Route::get('/system', [SystemCommandController::class, 'index'])->name('system');
    Route::post('/system/run', [SystemCommandController::class, 'runCommand'])->name('system.run');
});
