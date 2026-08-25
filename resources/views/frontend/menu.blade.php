@extends('layouts.frontend')

@section('title', 'Our Menu — ' . $siteName)

@section('content')
    <div class="py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Banner -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-extrabold uppercase tracking-wider text-amber-600">Fresh & Handcrafted</span>
                <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    Our Delicious Menu
                </h1>
                <p class="text-gray-600 mt-2 text-sm sm:text-base">
                    Choose from our selection of fresh dishes prepared with authentic ingredients.
                </p>
            </div>

            <!-- Category Filter Pills -->
            <div class="flex items-center justify-center flex-wrap gap-2 sm:gap-3 mb-12">
                <a href="{{ route('menu') }}" class="px-5 py-2.5 rounded-full text-sm font-bold transition-all shadow-sm {{ empty($categorySlug) ? 'bg-amber-600 text-white shadow-amber-600/20' : 'bg-white text-gray-700 hover:bg-amber-50 hover:text-amber-700 border border-gray-200' }}">
                    All Items
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('menu', ['category' => $category->slug]) }}" class="px-5 py-2.5 rounded-full text-sm font-bold transition-all shadow-sm {{ $categorySlug === $category->slug ? 'bg-amber-600 text-white shadow-amber-600/20' : 'bg-white text-gray-700 hover:bg-amber-50 hover:text-amber-700 border border-gray-200' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200/80 p-8 max-w-lg mx-auto shadow-sm">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-lg font-bold text-gray-800">No items available</h3>
                    <p class="text-sm text-gray-500 mt-1">There are no dishes matching this category at the moment.</p>
                    <a href="{{ route('menu') }}" class="mt-6 inline-flex items-center px-4 py-2 text-xs font-bold text-amber-700 bg-amber-50 rounded-xl hover:bg-amber-100 transition">
                        View All Items
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($products as $product)
                        <div x-data="{ modalOpen: false, selectedVariantId: '{{ $product->variants->first()?->id }}', quantity: 1 }" class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-sm hover:shadow-lg transition duration-200 flex flex-col justify-between">
                            @if($product->image_url)
                                <div class="relative h-48 w-full overflow-hidden bg-gray-100">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                </div>
                            @endif

                            <div class="p-6 flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="inline-block px-2.5 py-0.5 text-xs font-semibold text-amber-800 bg-amber-50 rounded-full">
                                            {{ $product->category->name }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900">
                                        {{ $product->name }}
                                    </h3>
                                    @if($product->description)
                                        <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                                            {{ $product->description }}
                                        </p>
                                    @endif

                                    <!-- Variant Pill Preview for visibility -->
                                    @if($product->has_variants && $product->variants->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach($product->variants as $variant)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700">
                                                    {{ $variant->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-gray-500 block">
                                            {{ $product->has_variants ? 'Starts from' : 'Price' }}
                                        </span>
                                        <span class="text-xl font-black text-gray-900">
                                            {{ $currencySymbol }}{{ number_format($product->base_price, 2) }}
                                        </span>
                                    </div>

                                    @if($product->has_variants && $product->variants->isNotEmpty())
                                        <!-- Choose Options Button with Alpine Modal -->
                                        <button type="button" @click="modalOpen = true" class="inline-flex items-center px-4 py-2.5 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-xl transition shadow-sm">
                                            Choose Options
                                        </button>

                                        <!-- Alpine Variant Selection Modal -->
                                        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                <!-- Backdrop -->
                                                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="modalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 sm:p-8">
                                                    <div class="flex items-start justify-between mb-4">
                                                        <div>
                                                            <span class="text-xs font-semibold text-amber-800 bg-amber-50 px-2.5 py-0.5 rounded-full">
                                                                {{ $product->category->name }}
                                                            </span>
                                                            <h3 class="text-xl font-bold text-gray-900 mt-1" id="modal-title">
                                                                {{ $product->name }}
                                                            </h3>
                                                        </div>
                                                        <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
                                                    </div>

                                                    @if($product->description)
                                                        <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                                                            {{ $product->description }}
                                                        </p>
                                                    @endif

                                                    <form method="POST" action="{{ route('cart.add') }}" class="space-y-6">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                                                        <!-- Variants Radio Group -->
                                                        <div>
                                                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-3">
                                                                Select Size / Option
                                                            </label>
                                                            <div class="space-y-2">
                                                                @foreach($product->variants as $index => $variant)
                                                                    <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 cursor-pointer hover:bg-amber-50/50 transition has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50">
                                                                        <div class="flex items-center gap-3">
                                                                            <input type="radio" name="variant_id" value="{{ $variant->id }}" x-model="selectedVariantId" {{ $loop->first ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                                                                            <span class="text-sm font-semibold text-gray-800">{{ $variant->name }}</span>
                                                                        </div>
                                                                        <span class="text-sm font-bold text-gray-900">
                                                                            @if($variant->price_adjustment > 0)
                                                                                + {{ $currencySymbol }}{{ number_format($variant->price_adjustment, 2) }}
                                                                            @elseif($variant->price_adjustment < 0)
                                                                                - {{ $currencySymbol }}{{ number_format(abs($variant->price_adjustment), 2) }}
                                                                            @else
                                                                                Included
                                                                            @endif
                                                                        </span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>

                                                        <!-- Quantity Stepper -->
                                                        <div>
                                                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                                                                Quantity
                                                            </label>
                                                            <div class="flex items-center gap-3">
                                                                <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold flex items-center justify-center transition">-</button>
                                                                <input type="number" name="quantity" x-model.number="quantity" min="1" max="99" class="w-20 text-center font-bold border-gray-300 rounded-xl focus:border-amber-500 focus:ring-amber-500">
                                                                <button type="button" @click="quantity = quantity + 1" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold flex items-center justify-center transition">+</button>
                                                            </div>
                                                        </div>

                                                        <!-- Submit Button -->
                                                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                                                            <button type="button" @click="modalOpen = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800">
                                                                Cancel
                                                            </button>
                                                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm shadow-md transition">
                                                                Add to Cart
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Simple Product Add Form -->
                                        <form method="POST" action="{{ route('cart.add') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="inline-flex items-center px-4 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 rounded-xl shadow-sm transition">
                                                Add to Cart
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
