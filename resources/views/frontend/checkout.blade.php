@extends('layouts.frontend')

@section('title', 'Checkout — ' . $siteName)

@section('content')
    <div class="py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">
                Complete Your Order
            </h1>

            @if($errors->any())
                <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
                    <div class="font-bold mb-2">Please fix the following issues:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $defaultPayment = '';
                if ($gateways['cod']['enabled']) $defaultPayment = 'cash';
                elseif ($gateways['stripe']['enabled']) $defaultPayment = 'stripe';
                elseif ($gateways['square']['enabled']) $defaultPayment = 'square';
                elseif ($gateways['merchant']['enabled']) $defaultPayment = 'merchant';
            @endphp
            <form method="POST" action="{{ route('checkout.store') }}" x-data="{ orderType: '{{ old('order_type', 'pickup') }}', paymentMethod: '{{ old('payment_method', $defaultPayment) }}' }">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    <!-- Left Column: Customer & Delivery Details -->
                    <div class="lg:col-span-7 space-y-8">
                        <!-- Contact Information -->
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs font-black flex items-center justify-center">1</span>
                                <span>Customer Information</span>
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                            Phone Number <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium" placeholder="e.g. 555-1234">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Type & Fulfillment -->
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs font-black flex items-center justify-center">2</span>
                                <span>Fulfillment Method</span>
                            </h2>

                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <label class="flex flex-col p-4 rounded-xl border-2 cursor-pointer transition text-center" :class="orderType === 'pickup' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" name="order_type" value="pickup" x-model="orderType" class="sr-only">
                                    <span class="font-bold text-sm text-gray-900">In-Store Pickup</span>
                                    <span class="text-xs text-gray-500 mt-1">Ready in ~25 mins</span>
                                </label>

                                <label class="flex flex-col p-4 rounded-xl border-2 cursor-pointer transition text-center" :class="orderType === 'delivery' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" name="order_type" value="delivery" x-model="orderType" class="sr-only">
                                    <span class="font-bold text-sm text-gray-900">Doorstep Delivery</span>
                                    <span class="text-xs text-gray-500 mt-1">
                                        @if($deliveryFee > 0)
                                            + {{ $currencySymbol }}{{ number_format($deliveryFee, 2) }} fee
                                        @else
                                            Free delivery
                                        @endif
                                    </span>
                                </label>
                            </div>

                            <!-- Delivery Address Input -->
                            <div x-show="orderType === 'delivery'" x-cloak class="space-y-2 mb-6">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    Delivery Street Address <span class="text-red-500">*</span>
                                </label>
                                <textarea name="delivery_address" rows="3" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="Street address, apartment, suite, city, postal code...">{{ old('delivery_address') }}</textarea>
                            </div>

                            <!-- Special Notes -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
                                    Special Instructions / Order Notes (Optional)
                                </label>
                                <textarea name="order_notes" rows="2" class="w-full rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="e.g. Ring bell, extra sauce, allergies...">{{ old('order_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs font-black flex items-center justify-center">3</span>
                                <span>Payment Option</span>
                            </h2>

                            <div class="space-y-3">
                                @if($gateways['cod']['enabled'])
                                    <label class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition" :class="paymentMethod === 'cash' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="text-amber-600 focus:ring-amber-500">
                                            <div>
                                                <span class="font-bold text-sm text-gray-900 block">Cash / Pay at Restaurant</span>
                                                <span class="text-xs text-gray-500">Pay in person when receiving your order</span>
                                            </div>
                                        </div>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </label>
                                @endif

                                @if($gateways['stripe']['enabled'])
                                    <label class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition" :class="paymentMethod === 'stripe' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="stripe" x-model="paymentMethod" class="text-amber-600 focus:ring-amber-500">
                                            <div>
                                                <span class="font-bold text-sm text-gray-900 block">Credit / Debit Card (Stripe)</span>
                                                <span class="text-xs text-gray-500">Secure online card payment via Stripe</span>
                                            </div>
                                        </div>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    </label>
                                @endif

                                @if($gateways['square']['enabled'])
                                    <label class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition" :class="paymentMethod === 'square' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="square" x-model="paymentMethod" class="text-amber-600 focus:ring-amber-500">
                                            <div>
                                                <span class="font-bold text-sm text-gray-900 block">Square Web Payments</span>
                                                <span class="text-xs text-gray-500">Secure online payment via Square</span>
                                            </div>
                                        </div>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    </label>
                                @endif

                                @if($gateways['merchant']['enabled'])
                                    <label class="flex items-center justify-between p-4 rounded-xl border cursor-pointer transition" :class="paymentMethod === 'merchant' ? 'border-amber-600 bg-amber-50/50' : 'border-gray-200 hover:bg-gray-50'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" name="payment_method" value="merchant" x-model="paymentMethod" class="text-amber-600 focus:ring-amber-500">
                                            <div>
                                                <span class="font-bold text-sm text-gray-900 block">Secure Merchant Payment</span>
                                                <span class="text-xs text-gray-500">Pay using our specific merchant provider</span>
                                            </div>
                                        </div>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </label>
                                @endif

                                @if(!$gateways['cod']['enabled'] && !$gateways['stripe']['enabled'] && !$gateways['square']['enabled'] && !$gateways['merchant']['enabled'])
                                    <div class="p-4 bg-red-50 text-red-700 rounded-xl text-sm border border-red-200">
                                        <strong>No payment methods available!</strong><br>
                                        The site administrator has not configured any payment options yet. Please contact the restaurant to place your order.
                                    </div>
                                    <input type="hidden" name="payment_method" value="">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary Side Card -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8 sticky top-28">
                        <h2 class="text-xl font-black text-gray-900 tracking-tight mb-6">
                            Order Summary
                        </h2>

                        <!-- Line Items List -->
                        <div class="space-y-4 max-h-80 overflow-y-auto pr-1 mb-6 border-b border-gray-100 pb-6">
                            @foreach($cart as $item)
                                <div class="flex items-start justify-between gap-4 text-sm">
                                    <div>
                                        <div class="font-bold text-gray-900">
                                            {{ $item['quantity'] }}x {{ $item['product_name'] }}
                                        </div>
                                        @if(!empty($item['variant_name']))
                                            <div class="text-xs text-amber-800 font-medium mt-0.5">
                                                {{ $item['variant_name'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="font-bold text-gray-900 shrink-0">
                                        {{ $currencySymbol }}{{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Calculations -->
                        <div class="space-y-3 text-sm pb-6 border-b border-gray-100">
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-900">
                                    {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                                </span>
                            </div>

                            @if($taxRate > 0)
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Estimated Tax ({{ $taxRate }}%)</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $currencySymbol }}{{ number_format($tax, 2) }}
                                    </span>
                                </div>
                            @endif

                            <div x-show="orderType === 'delivery'" class="flex items-center justify-between text-gray-600">
                                <span>Delivery Fee</span>
                                <span class="font-bold text-gray-900">
                                    {{ $currencySymbol }}{{ number_format($deliveryFee, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="py-6 flex items-center justify-between">
                            <span class="text-base font-bold text-gray-900">Total Amount</span>
                            <span class="text-2xl font-black text-amber-600">
                                <span x-show="orderType !== 'delivery'">
                                    {{ $currencySymbol }}{{ number_format($total, 2) }}
                                </span>
                                <span x-show="orderType === 'delivery'" x-cloak>
                                    {{ $currencySymbol }}{{ number_format($total + $deliveryFee, 2) }}
                                </span>
                            </span>
                        </div>

                        <!-- Submit Order Button -->
                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-4 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold text-base shadow-lg shadow-amber-600/25 transition duration-150">
                            Place Order Now
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
