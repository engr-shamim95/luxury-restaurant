<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Product / Dish') }}
            </h2>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                {{ __('&larr; Back to Products') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ variants: [] }">
                @csrf

                <!-- Basic Details -->
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                        {{ __('Product Information') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('-- Select Category --') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="base_price" :value="__('Base Price ($)')" />
                            <x-text-input id="base_price" name="base_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('base_price')" placeholder="0.00" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="name" :value="__('Product / Dish Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="e.g. Margherita Wood-Fired Pizza" required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="slug" :value="__('URL Slug (leave empty to auto-generate)')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" placeholder="e.g. margherita-wood-fired-pizza" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="description" :value="__('Description / Ingredients')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Freshly crushed San Marzano tomatoes, buffalo mozzarella...">{{ old('description') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="image" :value="__('Product Image')" />
                            <input id="image" name="image" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_available" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_available', '1') ? 'checked' : '' }}>
                                <span class="ms-2 text-sm font-medium text-gray-700">{{ __('Product is in stock / available to order') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Product Variants / Addons (Sizes, crusts, toppings) -->
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ __('Variants, Sizes & Add-ons') }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ __('Add custom portions, sizes (e.g. Small/Large), or extra toppings with price adjustments.') }}</p>
                        </div>
                        <button type="button" @click="variants.push({ name: '', type: 'size', price_adjustment: '0.00' })" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-md text-xs font-semibold hover:bg-indigo-100 transition">
                            {{ __('+ Add Variant Row') }}
                        </button>
                    </div>

                    <div x-show="variants.length === 0" class="text-center py-6 text-sm text-gray-400">
                        {{ __('No variants defined. This item will sell at base price only.') }}
                    </div>

                    <template x-for="(variant, index) in variants" :key="index">
                        <div class="p-4 border rounded-md bg-gray-50 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <div class="md:col-span-2">
                                <label class="text-xs font-medium text-gray-600">{{ __('Variant / Option Name') }}</label>
                                <input type="text" :name="'variants[' + index + '][name]'" x-model="variant.name" placeholder="e.g. Large 16&quot;, Extra Cheese" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-600">{{ __('Type') }}</label>
                                <select :name="'variants[' + index + '][type]'" x-model="variant.type" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="size">{{ __('Size') }}</option>
                                    <option value="addon">{{ __('Add-on / Extra') }}</option>
                                    <option value="option">{{ __('Option') }}</option>
                                    <option value="spice_level">{{ __('Spice Level') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center space-x-2">
                                <div class="flex-1">
                                    <label class="text-xs font-medium text-gray-600">{{ __('Price Adj. ($)') }}</label>
                                    <input type="number" step="0.01" :name="'variants[' + index + '][price_adjustment]'" x-model="variant.price_adjustment" placeholder="0.00" class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                </div>
                                <button type="button" @click="variants.splice(index, 1)" class="text-red-500 hover:text-red-700 font-bold p-1 text-lg mt-4">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold uppercase text-gray-700 hover:bg-gray-200">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Save Product') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
