<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Orders Management') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters Bar -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-center">
                    <div>
                        <x-text-input name="search" type="text" class="w-full text-sm" :value="request('search')" placeholder="Search #, name, phone..." />
                    </div>

                    <div>
                        <select name="status" class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Order Statuses') }}</option>
                            @foreach($statuses as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" {{ request('status') === $statusKey ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="payment_status" class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Payment Statuses') }}</option>
                            @foreach($paymentStatuses as $payKey => $payLabel)
                                <option value="{{ $payKey }}" {{ request('payment_status') === $payKey ? 'selected' : '' }}>
                                    {{ $payLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="order_type" class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Order Types') }}</option>
                            <option value="pickup" {{ request('order_type') === 'pickup' ? 'selected' : '' }}>{{ __('Pickup') }}</option>
                            <option value="delivery" {{ request('order_type') === 'delivery' ? 'selected' : '' }}>{{ __('Delivery') }}</option>
                        </select>
                    </div>

                    <div class="flex space-x-2">
                        <x-primary-button class="justify-center flex-1">
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 flex items-center justify-center">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Order #') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Customer') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Type') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Total') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Payment') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Order Status') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Placed At') }}</th>
                            <th class="px-6 py-3 text-right tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-600">
                                    <a href="{{ route('admin.orders.show', $order) }}">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->customer_phone ?? $order->customer_email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $order->order_type === 'delivery' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($order->order_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                    {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $payBadge = match($order->payment_status) {
                                            'paid' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-amber-100 text-amber-800',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $payBadge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                    <span class="text-xs text-gray-400 block mt-0.5">({{ strtoupper($order->payment_method) }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusBadge = match($order->order_status) {
                                            'new' => 'bg-amber-100 text-amber-800',
                                            'preparing' => 'bg-blue-100 text-blue-800',
                                            'ready' => 'bg-indigo-100 text-indigo-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusBadge }}">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ $order->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    {{ __('No orders found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($orders->hasPages())
                    <div class="p-4 border-t">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
