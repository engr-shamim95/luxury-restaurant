<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->hasHeader('CF-Ray')) {
            \Illuminate\Support\Facades\URL::forceRootUrl('https://restaurant.engrshamim.shop');
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            try {
                $siteName = \App\Models\Setting::get('restaurant_name')
                    ?: (\App\Models\Setting::get('site_name')
                    ?: (\App\Models\Setting::get('store_name')
                    ?: config('app.name', 'Restaurant')));

                $siteTagline = \App\Models\Setting::get('site_tagline')
                    ?: (\App\Models\Setting::get('store_tagline')
                    ?: \App\Models\Setting::get('site_description'));

                $rawSiteLogo = \App\Models\Setting::get('site_logo') ?: \App\Models\Setting::get('store_logo');
                $siteLogo = $rawSiteLogo ? (str_starts_with($rawSiteLogo, 'http') ? $rawSiteLogo : asset('storage/' . ltrim($rawSiteLogo, '/'))) : null;

                $sitePhone = \App\Models\Setting::get('restaurant_phone')
                    ?: (\App\Models\Setting::get('contact_phone')
                    ?: (\App\Models\Setting::get('store_phone')
                    ?: \App\Models\Setting::get('phone')));

                $siteEmail = \App\Models\Setting::get('contact_email')
                    ?: (\App\Models\Setting::get('store_email')
                    ?: \App\Models\Setting::get('email'));

                $siteAddress = \App\Models\Setting::get('contact_address')
                    ?: (\App\Models\Setting::get('store_address')
                    ?: \App\Models\Setting::get('address'));

                $openingHours = \App\Models\Setting::get('opening_hours');
                $currencySymbol = \App\Models\Setting::get('currency_symbol', '$');
                $taxRate = (float) \App\Models\Setting::get('tax_rate', 0);
                $deliveryFee = (float) \App\Models\Setting::get('delivery_fee', 0);
                $facebookUrl = \App\Models\Setting::get('facebook_url');
                $instagramUrl = \App\Models\Setting::get('instagram_url');
                $twitterUrl = \App\Models\Setting::get('twitter_url');
                $copyrightText = \App\Models\Setting::get('copyright_text');

                $headerMenu = \App\Models\NavigationMenu::getByLocation('header');
                $footerMenu = \App\Models\NavigationMenu::getByLocation('footer');

                $cart = session('cart', []);
                $cartCount = is_array($cart) ? array_sum(array_column($cart, 'quantity')) : 0;

                $view->with([
                    'siteName' => $siteName,
                    'siteTagline' => $siteTagline,
                    'siteLogo' => $siteLogo,
                    'sitePhone' => $sitePhone,
                    'siteEmail' => $siteEmail,
                    'siteAddress' => $siteAddress,
                    'openingHours' => $openingHours,
                    'currencySymbol' => $currencySymbol,
                    'taxRate' => $taxRate,
                    'deliveryFee' => $deliveryFee,
                    'facebookUrl' => $facebookUrl,
                    'instagramUrl' => $instagramUrl,
                    'twitterUrl' => $twitterUrl,
                    'copyrightText' => $copyrightText,
                    'headerMenu' => $headerMenu,
                    'footerMenu' => $footerMenu,
                    'cartCount' => $cartCount,
                ]);
            } catch (\Throwable $e) {
                // Ignore DB errors during early artisan bootstrap / migrations
            }
        });
    }
}
