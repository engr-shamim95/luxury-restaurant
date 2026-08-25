<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products & Dishes Catalog') }}
            </h2>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('+ Add Product') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search & Filters Bar -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <form action="{{ route('admin.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    <div class="md:col-span-2">
                        <x-text-input name="search" type="text" class="w-full" :value="request('search')" placeholder="Search dish name or description..." />
                    </div>

                    <div>
                        <select name="category_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex space-x-2">
                        <x-primary-button class="justify-center flex-1">
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 flex items-center justify-center">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Products Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Product / Dish') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Category') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Base Price') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Variants') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Availability') }}</th>
                            <th class="px-6 py-3 text-right tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-md me-3 border" />
                                        @else
                                            <div class="w-12 h-12 rounded-md bg-gray-100 flex items-center justify-center me-3 text-gray-400 font-bold border">
                                                🍕
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500 max-w-xs truncate">{{ $product->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                    {{ \App\Models\Setting::get('currency_symbol', '$') }}{{ number_format($product->base_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->has_variants && $product->variants->count() > 0)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            {{ $product->variants->count() }} {{ __('options') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">{{ __('Single Item') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->is_available)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Available') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Sold Out') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    {{ __('No products found matching your query.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($products->hasPages())
                    <div class="p-4 border-t">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
