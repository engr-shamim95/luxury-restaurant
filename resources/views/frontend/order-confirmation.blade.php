@extends('layouts.frontend')

@section('title', 'Order Confirmation — ' . $siteName)

@section('content')
    <div class="py-10 sm:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Confirmation Banner -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-8 sm:p-12 mb-8 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>

                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-600">Order Received</span>
                <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1 mb-2">
                    Thank You, {{ $order->customer_name }}!
                </h1>
                <p class="text-gray-600 max-w-lg mx-auto text-sm sm:text-base">
                    Your order has been sent to our kitchen. We'll have your food freshly prepared with care.
                </p>

                <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-center gap-6 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs">Order Reference</span>
                        <span class="font-black text-gray-900 text-base">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    @if($order->transaction_id)
                        <div>
                            <span class="text-gray-500 block text-xs">Transaction ID</span>
                            <span class="font-mono font-bold text-gray-800 text-sm">{{ $order->transaction_id }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-gray-500 block text-xs">Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            {{ ucfirst($order->order_status) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Fulfillment</span>
                        <span class="font-bold text-gray-900 capitalize">{{ $order->order_type }}</span>
                    </div>
                </div>
            </div>

            <!-- Receipt & Order Details Card -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6 sm:p-8 space-y-8">
                <!-- Customer & Delivery Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-gray-100 text-sm">
                    <div>
                        <h3 class="font-bold uppercase tracking-wider text-xs text-gray-500 mb-3">Customer Details</h3>
                        <p class="font-semibold text-gray-900">{{ $order->customer_name }}</p>
                        <p class="text-gray-600">{{ $order->customer_email }}</p>
                        <p class="text-gray-600">{{ $order->customer_phone }}</p>
                    </div>

                    <div>
                        <h3 class="font-bold uppercase tracking-wider text-xs text-gray-500 mb-3">Fulfillment Details</h3>
                        @if($order->order_type === 'delivery')
                            <p class="text-gray-700 leading-relaxed font-medium">
                                {{ $order->delivery_address }}
                            </p>
                        @else
                            <p class="text-gray-700 font-medium">In-Store Pickup</p>
                            @if($siteAddress)
                                <p class="text-xs text-gray-500 mt-1">{{ $siteAddress }}</p>
                            @endif
                        @endif

                        @if($order->order_notes)
                            <div class="mt-3 p-3 bg-amber-50 rounded-xl text-xs text-amber-900">
                                <span class="font-bold">Instructions:</span> {{ $order->order_notes }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Itemized Breakdown -->
                <div>
                    <h3 class="font-bold uppercase tracking-wider text-xs text-gray-500 mb-4">Itemized Receipt</h3>
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between gap-4 py-2 border-b border-gray-50 last:border-b-0 text-sm">
                                <div>
                                    <div class="font-bold text-gray-900">
                                        {{ $item->quantity }}x {{ $item->product_name }}
                                    </div>
                                    @if(!empty($item->variants_selected['name']))
                                        <div class="text-xs text-amber-800 font-medium mt-0.5">
                                            {{ $item->variants_selected['name'] }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">
                                        {{ $currencySymbol }}{{ number_format($item->total_price, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ({{ $currencySymbol }}{{ number_format($item->unit_price, 2) }} each)
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Totals Section -->
                <div class="pt-4 border-t border-gray-100 space-y-2 text-sm">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    @if($order->tax > 0)
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Tax</span>
                            <span class="font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($order->tax, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-lg">
                        <span class="font-bold text-gray-900">Total Paid / Due</span>
                        <span class="font-black text-amber-600 text-2xl">
                            {{ $currencySymbol }}{{ number_format($order->total, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('menu') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md transition">
                        &larr; Order More Items
                    </a>

                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Print Receipt</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
