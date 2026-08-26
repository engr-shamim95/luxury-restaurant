<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    {{ __('Manage Orders') }}
                </a>
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                    {{ __('+ New Product') }}
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

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Sales -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-emerald-500">
                    <div class="text-sm font-medium text-gray-500">{{ __('Total Revenue') }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($totalSales, 2) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ __('Today:') }} {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($todaySales, 2) }}
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500">{{ __('Total Orders') }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalOrders }}</div>
                    <div class="mt-1 text-xs text-blue-600">
                        <a href="{{ route('admin.orders.index') }}" class="hover:underline">{{ __('View all orders &rarr;') }}</a>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-amber-500">
                    <div class="text-sm font-medium text-gray-500">{{ __('Pending / Kitchen') }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingOrders }}</div>
                    <div class="mt-1 text-xs text-amber-600 font-medium">
                        {{ __('Requires attention') }}
                    </div>
                </div>

                <!-- Menu Items / Products -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border-l-4 border-purple-500">
                    <div class="text-sm font-medium text-gray-500">{{ __('Menu Catalog') }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalProducts }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ $totalCategories }} {{ __('Categories') }}
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Shortcuts -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="{{ route('admin.orders.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">📦</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('Orders') }}</div>
                </a>
                <a href="{{ route('admin.products.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">🍕</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('Products') }}</div>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">📂</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('Categories') }}</div>
                </a>
                <a href="{{ route('admin.pages.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">📄</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('CMS Pages') }}</div>
                </a>
                <a href="{{ route('admin.navigation.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">🧭</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('Navigation') }}</div>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="bg-white p-4 text-center rounded-lg shadow-sm hover:shadow-md hover:border-indigo-400 border transition">
                    <div class="text-2xl mb-1">⚙️</div>
                    <div class="font-medium text-sm text-gray-800">{{ __('Settings') }}</div>
                </a>
            </div>

            <!-- Recent Orders Section -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden overflow-x-auto">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-lg text-gray-800">{{ __('Recent Orders') }}</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        {{ __('View All Orders &rarr;') }}
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Order #') }}</th>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Customer') }}</th>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Total') }}</th>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left tracking-wider">{{ __('Date') }}</th>
                                <th class="px-6 py-3 text-right tracking-wider">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-indigo-600">
                                        <a href="{{ route('admin.orders.show', $order) }}">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        {{ $order->customer_name }}
                                        <div class="text-xs text-gray-500">{{ $order->customer_phone ?? $order->customer_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $order->order_type === 'delivery' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($order->order_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">
                                        {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'new' => 'bg-amber-100 text-amber-800',
                                                'preparing' => 'bg-blue-100 text-blue-800',
                                                'ready' => 'bg-indigo-100 text-indigo-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->order_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($order->order_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $order->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ __('View Details') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        {{ __('No orders placed yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
