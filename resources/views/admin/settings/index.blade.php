<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Restaurant & Platform Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- 1. Restaurant Identity -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
                        <span class="mr-2">🏪</span> {{ __('Restaurant Identity & Branding') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="site_name" :value="__('Restaurant Name / Site Title')" />
                            <x-text-input id="site_name" name="site_name" type="text" class="mt-1 block w-full" :value="old('site_name', $settings['site_name'] ?? $settings['restaurant_name'] ?? '')" required />
                        </div>

                        <div>
                            <x-input-label for="site_tagline" :value="__('Tagline / Slogan')" />
                            <x-text-input id="site_tagline" name="site_tagline" type="text" class="mt-1 block w-full" :value="old('site_tagline', $settings['site_tagline'] ?? '')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="hero_title" :value="__('Homepage Hero Title')" />
                            <x-text-input id="hero_title" name="hero_title" type="text" class="mt-1 block w-full" :value="old('hero_title', $settings['hero_title'] ?? '')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="hero_subtitle" :value="__('Homepage Hero Subtitle / Description')" />
                            <textarea id="hero_subtitle" name="hero_subtitle" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}</textarea>
                        </div>

                        <div>
                            <x-input-label for="site_logo" :value="__('Restaurant Logo')" />
                            <input id="site_logo" name="site_logo" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @if(!empty($settings['site_logo']))
                                <div class="mt-2 flex items-center space-x-2">
                                    <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" class="h-10 w-auto rounded border" />
                                    <span class="text-xs text-gray-500">{{ __('Current Logo') }}</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <x-input-label for="site_favicon" :value="__('Favicon Icon')" />
                            <input id="site_favicon" name="site_favicon" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @if(!empty($settings['site_favicon']))
                                <div class="mt-2 flex items-center space-x-2">
                                    <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Favicon" class="h-6 w-6 rounded border" />
                                    <span class="text-xs text-gray-500">{{ __('Current Favicon') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Contact Information & Operating Hours -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
                        <span class="mr-2">📍</span> {{ __('Contact Details & Operating Hours') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="contact_email" :value="__('Contact Email Address')" />
                            <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $settings['contact_email'] ?? $settings['restaurant_email'] ?? '')" />
                        </div>

                        <div>
                            <x-input-label for="contact_phone" :value="__('Contact Phone Number')" />
                            <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" :value="old('contact_phone', $settings['contact_phone'] ?? $settings['restaurant_phone'] ?? '')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="contact_address" :value="__('Physical Restaurant Address')" />
                            <x-text-input id="contact_address" name="contact_address" type="text" class="mt-1 block w-full" :value="old('contact_address', $settings['contact_address'] ?? $settings['restaurant_address'] ?? '')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="opening_hours" :value="__('Weekly Opening Hours Schedule')" />
                            <x-text-input id="opening_hours" name="opening_hours" type="text" class="mt-1 block w-full" :value="old('opening_hours', $settings['opening_hours'] ?? '')" placeholder="e.g. Mon-Sun: 11:00 AM - 10:00 PM" />
                        </div>
                    </div>
                </div>

                <!-- 3. Ordering, Tax & Pricing -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
                        <span class="mr-2">💳</span> {{ __('Currency, Taxes & Delivery Fees') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="currency_symbol" :value="__('Currency Symbol')" />
                            <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1 block w-full" :value="old('currency_symbol', $settings['currency_symbol'] ?? '$')" required />
                        </div>

                        <div>
                            <x-input-label for="currency_code" :value="__('Currency Code (ISO)')" />
                            <x-text-input id="currency_code" name="currency_code" type="text" class="mt-1 block w-full" :value="old('currency_code', $settings['currency_code'] ?? 'USD')" required />
                        </div>

                        <div>
                            <x-input-label for="tax_rate" :value="__('Sales Tax Rate (%)')" />
                            <x-text-input id="tax_rate" name="tax_rate" type="number" step="0.01" class="mt-1 block w-full" :value="old('tax_rate', $settings['tax_rate'] ?? $settings['tax_rate_percent'] ?? '0')" />
                        </div>

                        <div>
                            <x-input-label for="delivery_fee" :value="__('Fixed Delivery Fee')" />
                            <x-text-input id="delivery_fee" name="delivery_fee" type="number" step="0.01" class="mt-1 block w-full" :value="old('delivery_fee', $settings['delivery_fee'] ?? '0.00')" />
                        </div>

                        <div>
                            <x-input-label for="minimum_order_amount" :value="__('Minimum Order Total')" />
                            <x-text-input id="minimum_order_amount" name="minimum_order_amount" type="number" step="0.01" class="mt-1 block w-full" :value="old('minimum_order_amount', $settings['minimum_order_amount'] ?? '0.00')" />
                        </div>

                        <div class="space-y-2 pt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="enable_pickup" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ !empty($settings['enable_pickup']) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Enable Pickup Orders') }}</span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="enable_delivery" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ !empty($settings['enable_delivery']) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Enable Delivery Orders') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 4. Social Media Links -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
                        <span class="mr-2">🌐</span> {{ __('Social Media Links') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="facebook_url" :value="__('Facebook URL')" />
                            <x-text-input id="facebook_url" name="facebook_url" type="url" class="mt-1 block w-full" :value="old('facebook_url', $settings['facebook_url'] ?? '')" placeholder="https://facebook.com/your-restaurant" />
                        </div>

                        <div>
                            <x-input-label for="instagram_url" :value="__('Instagram URL')" />
                            <x-text-input id="instagram_url" name="instagram_url" type="url" class="mt-1 block w-full" :value="old('instagram_url', $settings['instagram_url'] ?? '')" placeholder="https://instagram.com/your-restaurant" />
                        </div>

                        <div>
                            <x-input-label for="twitter_url" :value="__('Twitter / X URL')" />
                            <x-text-input id="twitter_url" name="twitter_url" type="url" class="mt-1 block w-full" :value="old('twitter_url', $settings['twitter_url'] ?? '')" placeholder="https://x.com/your-restaurant" />
                        </div>
                    </div>
                </div>

                <!-- 5. Payment Gateways (API Settings) -->
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
                        <span class="mr-2">🏦</span> {{ __('Payment Gateways (API Settings)') }}
                    </h3>
                    
                    <p class="text-sm text-gray-500 mb-6">
                        Manage how customers pay. You can simply paste your API keys below to enable a provider. The system will automatically detect and show available gateways on the checkout page. If no gateways are enabled, customers will only see Cash on Delivery (if enabled).
                    </p>

                    <!-- Cash on Delivery -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="cod_enabled" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5" {{ !empty($settings['cod_enabled']) ? 'checked' : '' }}>
                            <span class="ms-3 text-base font-bold text-gray-900">{{ __('Enable Cash / Pay at Restaurant') }}</span>
                        </label>
                        <p class="text-sm text-gray-500 mt-1 ml-8">Allow customers to place an order and pay when they pick up or upon delivery.</p>
                    </div>

                    <!-- Stripe -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="inline-flex items-center mb-4">
                            <input type="checkbox" name="stripe_enabled" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5" {{ !empty($settings['stripe_enabled']) ? 'checked' : '' }}>
                            <span class="ms-3 text-base font-bold text-gray-900">Enable Stripe Gateway</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                            <div>
                                <x-input-label for="stripe_public_key" :value="__('Stripe Publishable Key')" />
                                <x-text-input id="stripe_public_key" name="stripe_public_key" type="text" class="mt-1 block w-full" :value="old('stripe_public_key', $settings['stripe_public_key'] ?? '')" placeholder="pk_test_..." />
                            </div>
                            <div>
                                <x-input-label for="stripe_secret_key" :value="__('Stripe Secret Key')" />
                                <x-text-input id="stripe_secret_key" name="stripe_secret_key" type="password" class="mt-1 block w-full" :value="old('stripe_secret_key', $settings['stripe_secret_key'] ?? '')" placeholder="sk_test_..." />
                            </div>
                        </div>
                    </div>

                    <!-- Square -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="inline-flex items-center mb-4">
                            <input type="checkbox" name="square_enabled" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5" {{ !empty($settings['square_enabled']) ? 'checked' : '' }}>
                            <span class="ms-3 text-base font-bold text-gray-900">Enable Square Web Payments</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ml-8">
                            <div>
                                <x-input-label for="square_app_id" :value="__('Square Application ID')" />
                                <x-text-input id="square_app_id" name="square_app_id" type="text" class="mt-1 block w-full" :value="old('square_app_id', $settings['square_app_id'] ?? '')" placeholder="sq0idp-..." />
                            </div>
                            <div>
                                <x-input-label for="square_access_token" :value="__('Square Access Token')" />
                                <x-text-input id="square_access_token" name="square_access_token" type="password" class="mt-1 block w-full" :value="old('square_access_token', $settings['square_access_token'] ?? '')" placeholder="EAAAE..." />
                            </div>
                            <div>
                                <x-input-label for="square_location_id" :value="__('Square Location ID')" />
                                <x-text-input id="square_location_id" name="square_location_id" type="text" class="mt-1 block w-full" :value="old('square_location_id', $settings['square_location_id'] ?? '')" placeholder="L..." />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Third Provider (Placeholder) -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 opacity-60">
                        <label class="inline-flex items-center mb-4">
                            <input type="checkbox" name="merchant_enabled" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5" {{ !empty($settings['merchant_enabled']) ? 'checked' : '' }}>
                            <span class="ms-3 text-base font-bold text-gray-900">Enable Specific Merchant Provider (Pending API Docs)</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8">
                            <div>
                                <x-input-label for="merchant_api_key" :value="__('Merchant API Key')" />
                                <x-text-input id="merchant_api_key" name="merchant_api_key" type="text" class="mt-1 block w-full bg-gray-100" :value="old('merchant_api_key', $settings['merchant_api_key'] ?? '')" readonly />
                            </div>
                            <div>
                                <x-input-label for="merchant_secret" :value="__('Merchant Secret')" />
                                <x-text-input id="merchant_secret" name="merchant_secret" type="password" class="mt-1 block w-full bg-gray-100" :value="old('merchant_secret', $settings['merchant_secret'] ?? '')" readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button class="py-3 px-6 text-sm">
                        {{ __('Save Settings') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
