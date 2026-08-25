<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Product: ') }} {{ $product->name }}
            </h2>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                {{ __('&larr; Back to Products') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Basic Product Information -->
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                        {{ __('Product Details') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="base_price" :value="__('Base Price ($)')" />
                            <x-text-input id="base_price" name="base_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('base_price', $product->base_price)" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="name" :value="__('Product / Dish Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name)" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="slug" :value="__('URL Slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $product->slug)" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $product->description) }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="image" :value="__('Product Image')" />
                            <input id="image" name="image" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @if($product->image)
                                <div class="mt-2 flex items-center space-x-2">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-12 w-12 object-cover rounded border" />
                                    <span class="text-xs text-gray-500">{{ __('Current Image') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_available" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_available', $product->is_available) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm font-medium text-gray-700">{{ __('Product is in stock / available to order') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold uppercase text-gray-700 hover:bg-gray-200">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Update Product') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Existing Variants Management Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                <div class="border-b pb-3">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Product Variants & Pricing Adjustments') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Manage sizes (Small, Medium, Large) or add-ons (Extra Cheese, Bacon, etc.).') }}</p>
                </div>

                <!-- Existing Variants List -->
                <div class="divide-y divide-gray-100">
                    @forelse($product->variants as $variant)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-semibold text-gray-900">{{ $variant->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600 uppercase font-mono ms-2">{{ $variant->type }}</span>
                            </div>

                            <div class="flex items-center space-x-4">
                                <span class="font-mono text-sm font-semibold {{ (float) $variant->price_adjustment >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ (float) $variant->price_adjustment >= 0 ? '+' : '' }}${{ number_format($variant->price_adjustment, 2) }}
                                </span>

                                <form action="{{ route('admin.variants.destroy', $variant) }}" method="POST" onsubmit="return confirm('Delete this variant?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-sm text-gray-500">
                            {{ __('No variants added yet for this product.') }}
                        </div>
                    @endforelse
                </div>

                <!-- Add New Variant Form -->
                <div class="border-t pt-4 bg-gray-50 p-4 rounded-md">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">{{ __('+ Add New Variant') }}</h4>
                    <form action="{{ route('admin.products.variants.store', $product) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <div class="md:col-span-2">
                            <x-input-label for="variant_name" :value="__('Variant Name')" />
                            <x-text-input id="variant_name" name="name" type="text" class="mt-1 block w-full text-sm" placeholder="e.g. Large 16&quot;" required />
                        </div>

                        <div>
                            <x-input-label for="variant_type" :value="__('Type')" />
                            <select id="variant_type" name="type" class="mt-1 block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="size">{{ __('Size') }}</option>
                                <option value="addon">{{ __('Add-on') }}</option>
                                <option value="option">{{ __('Option') }}</option>
                                <option value="spice_level">{{ __('Spice Level') }}</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="variant_adj" :value="__('Price Adjustment ($)')" />
                            <x-text-input id="variant_adj" name="price_adjustment" type="number" step="0.01" class="mt-1 block w-full text-sm" placeholder="0.00" required />
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-700">
                                {{ __('Add Variant') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
