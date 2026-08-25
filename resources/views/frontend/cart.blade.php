@extends('layouts.frontend')

@section('title', 'Shopping Cart — ' . $siteName)

@section('content')
    <div class="py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">
                Your Shopping Cart
            </h1>

            @if(empty($cart))
                <div class="bg-white rounded-2xl border border-gray-200/80 p-12 text-center max-w-lg mx-auto shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-sm text-gray-500 mb-6">Looks like you haven't added any delicious dishes yet.</p>
                    <a href="{{ route('menu') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md transition">
                        Explore Our Menu
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    <!-- Cart Items Column -->
                    <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="hidden sm:grid sm:grid-cols-12 text-xs font-extrabold uppercase tracking-wider text-gray-500 pb-4 border-b border-gray-100">
                                <div class="col-span-6">Item Details</div>
                                <div class="col-span-2 text-center">Unit Price</div>
                                <div class="col-span-2 text-center">Quantity</div>
                                <div class="col-span-2 text-right">Subtotal</div>
                            </div>

                            @foreach($cart as $key => $item)
                                <div class="grid grid-cols-1 sm:grid-cols-12 items-center gap-4 py-4 border-b border-gray-100 last:border-b-0">
                                    <!-- Item Name & Variant -->
                                    <div class="sm:col-span-6">
                                        <h3 class="font-bold text-base text-gray-900">
                                            {{ $item['product_name'] }}
                                        </h3>
                                        @if(!empty($item['variant_name']))
                                            <span class="inline-block text-xs font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md mt-1">
                                                {{ $item['variant_name'] }}
                                            </span>
                                        @endif

                                        <form method="POST" action="{{ route('cart.remove', $item['item_key'] ?? $key) }}" class="mt-2 inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 transition">
                                                Remove Item
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="sm:col-span-2 text-left sm:text-center text-sm font-semibold text-gray-700">
                                        <span class="sm:hidden text-xs text-gray-500">Price: </span>
                                        {{ $currencySymbol }}{{ number_format($item['price'], 2) }}
                                    </div>

                                    <!-- Quantity Stepper -->
                                    <div class="sm:col-span-2 flex items-center justify-start sm:justify-center">
                                        <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                                            <form method="POST" action="{{ route('cart.update') }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="item_key" value="{{ $item['item_key'] ?? $key }}">
                                                <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 text-gray-800 font-bold flex items-center justify-center text-xs shadow-sm transition">-</button>
                                            </form>

                                            <span class="w-8 text-center text-sm font-bold text-gray-800">
                                                {{ $item['quantity'] }}
                                            </span>

                                            <form method="POST" action="{{ route('cart.update') }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="item_key" value="{{ $item['item_key'] ?? $key }}">
                                                <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 text-gray-800 font-bold flex items-center justify-center text-xs shadow-sm transition">+</button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="sm:col-span-2 text-left sm:text-right font-black text-gray-900 text-base">
                                        <span class="sm:hidden text-xs font-normal text-gray-500">Subtotal: </span>
                                        {{ $currencySymbol }}{{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Bottom Cart Actions -->
                        <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                            <a href="{{ route('menu') }}" class="inline-flex items-center text-sm font-bold text-amber-700 hover:text-amber-800">
                                &larr; Continue Ordering
                            </a>
                            <form method="POST" action="{{ route('cart.clear') }}">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-gray-500 hover:text-red-600 transition" onclick="return confirm('Are you sure you want to clear your cart?')">
                                    Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8 sticky top-28">
                        <h2 class="text-xl font-black text-gray-900 tracking-tight mb-6">
                            Order Summary
                        </h2>

                        <div class="space-y-4 text-sm pb-6 border-b border-gray-100">
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-bold text-gray-900">
                                    {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                                </span>
                            </div>

                            @if($taxRate > 0)
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Tax ({{ $taxRate }}%)</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $currencySymbol }}{{ number_format($tax, 2) }}
                                    </span>
                                </div>
                            @endif

                            @if($deliveryFee > 0)
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Est. Delivery Fee</span>
                                    <span class="font-bold text-gray-900">
                                        {{ $currencySymbol }}{{ number_format($deliveryFee, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="py-6 flex items-center justify-between">
                            <span class="text-base font-bold text-gray-900">Estimated Total</span>
                            <span class="text-2xl font-black text-amber-600">
                                {{ $currencySymbol }}{{ number_format($total, 2) }}
                            </span>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="w-full inline-flex items-center justify-center px-6 py-4 rounded-xl bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold text-base shadow-lg shadow-amber-600/25 transition duration-150">
                            Proceed to Checkout &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
