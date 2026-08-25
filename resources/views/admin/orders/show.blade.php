<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details: ') }} #{{ $order->id }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('🖨️ Print Receipt') }}
                </a>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('&larr; Back to Orders') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Customer & Status Panel -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Status Updater Card -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">
                            {{ __('Update Status') }}
                        </h3>

                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <x-input-label for="order_status" :value="__('Order Workflow Status')" />
                                <select id="order_status" name="order_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($statuses as $statusKey => $statusLabel)
                                        <option value="{{ $statusKey }}" {{ $order->order_status === $statusKey ? 'selected' : '' }}>
                                            {{ $statusLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="payment_status" :value="__('Payment Status')" />
                                <select id="payment_status" name="payment_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($paymentStatuses as $payKey => $payLabel)
                                        <option value="{{ $payKey }}" {{ $order->payment_status === $payKey ? 'selected' : '' }}>
                                            {{ $payLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <x-primary-button class="w-full justify-center">
                                {{ __('Update Order Status') }}
                            </x-primary-button>
                        </form>
                    </div>

                    <!-- Customer Information Card -->
                    <div class="bg-white shadow-sm rounded-lg p-6 space-y-3">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                            {{ __('Customer Information') }}
                        </h3>

                        <div>
                            <div class="text-xs text-gray-500">{{ __('Customer Name') }}</div>
                            <div class="font-semibold text-gray-900">{{ $order->customer_name }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">{{ __('Email Address') }}</div>
                            <div class="text-sm text-gray-900">{{ $order->customer_email }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">{{ __('Phone Number') }}</div>
                            <div class="text-sm text-gray-900">{{ $order->customer_phone ?? 'N/A' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">{{ __('Fulfillment Type') }}</div>
                            <div class="text-sm font-semibold capitalize {{ $order->order_type === 'delivery' ? 'text-purple-600' : 'text-blue-600' }}">
                                {{ $order->order_type }}
                            </div>
                        </div>

                        @if($order->order_type === 'delivery')
                            <div>
                                <div class="text-xs text-gray-500">{{ __('Delivery Address') }}</div>
                                <div class="text-sm text-gray-900 bg-gray-50 p-2 rounded border mt-1">
                                    {{ $order->delivery_address ?? 'No address provided' }}
                                </div>
                            </div>
                        @endif

                        @if($order->order_notes)
                            <div>
                                <div class="text-xs text-gray-500">{{ __('Customer Special Instructions / Notes') }}</div>
                                <div class="text-sm text-amber-900 bg-amber-50 p-2 rounded border border-amber-200 mt-1">
                                    {{ $order->order_notes }}
                                </div>
                            </div>
                        @endif

                        <div class="pt-2 border-t text-xs text-gray-400">
                            {{ __('Order Placed: ') }} {{ $order->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>

                <!-- Right Column: Order Items & Receipt Breakdown -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="p-6 border-b">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Ordered Items') }}</h3>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <div class="p-6 flex justify-between items-start">
                                    <div class="space-y-1">
                                        <div class="font-bold text-gray-900 text-base">
                                            {{ $item->quantity }}x {{ $item->product_name }}
                                        </div>

                                        @if(!empty($item->variants_selected))
                                            <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded space-y-0.5">
                                                @if(is_array($item->variants_selected))
                                                    @if(isset($item->variants_selected['name']))
                                                        <div>• <span class="font-medium">{{ $item->variants_selected['name'] }}</span></div>
                                                    @else
                                                        @foreach($item->variants_selected as $v)
                                                            <div>
                                                                • <span class="font-medium">{{ is_array($v) ? ($v['name'] ?? '') : $v }}</span>
                                                                @if(is_array($v) && !empty($v['price_adjustment']) && (float)$v['price_adjustment'] > 0)
                                                                    (+{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($v['price_adjustment'], 2) }})
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @else
                                                    <div>• <span class="font-medium">{{ $item->variants_selected }}</span></div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="text-xs text-gray-500">
                                            {{ __('Unit Price:') }} {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($item->unit_price, 2) }}
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="font-bold text-base text-gray-900">
                                            {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($item->total_price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Financial Summary -->
                        <div class="p-6 bg-gray-50 border-t space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Subtotal') }}</span>
                                <span class="font-medium">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->subtotal, 2) }}</span>
                            </div>

                            <div class="flex justify-between text-sm text-gray-600">
                                <span>{{ __('Tax') }}</span>
                                <span class="font-medium">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->tax, 2) }}</span>
                            </div>

                            @php
                                $deliveryFee = (float) $order->total - ((float) $order->subtotal + (float) $order->tax);
                            @endphp
                            @if($deliveryFee > 0.01)
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Delivery Fee') }}</span>
                                    <span class="font-medium">{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($deliveryFee, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-lg font-bold text-gray-900 border-t pt-2">
                                <span>{{ __('Grand Total') }}</span>
                                <span>{{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->total, 2) }}</span>
                            </div>

                            <div class="text-xs text-gray-500 pt-1">
                                {{ __('Payment Method:') }} <span class="font-semibold uppercase">{{ $order->payment_method }}</span>
                                ({{ ucfirst($order->payment_status) }})
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
