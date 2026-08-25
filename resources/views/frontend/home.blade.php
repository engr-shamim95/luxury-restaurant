@extends('layouts.frontend')

@section('title', $siteName . ' — ' . ($siteTagline ?? 'Authentic & Fresh Dining'))

@section('content')
    {{-- ═══════════════════════════════════════════════════
        HERO — Full-viewport cinematic entrance
    ═══════════════════════════════════════════════════ --}}
    <section class="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden bg-gray-950">
        {{-- Background image --}}
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-40 scale-105"
             style="background-image: url('{{ asset('storage/settings/hero-bg.jpg') }}');"></div>

        {{-- Layered gradient overlays for depth --}}
        <div class="absolute inset-0 bg-gradient-to-b from-gray-950/70 via-transparent to-gray-950"></div>
        <div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-gray-950 to-transparent"></div>

        {{-- Hero content --}}
        <div class="relative z-10 text-center px-6 max-w-5xl mx-auto">
            <p class="text-amber-400 tracking-[0.35em] uppercase text-[11px] sm:text-xs font-semibold mb-8 animate-pulse">
                ✦ &ensp; Welcome to &ensp; ✦
            </p>

            <h1 class="text-5xl sm:text-7xl lg:text-[5.5rem] text-white leading-[1.05] mb-8 font-bold"
                style="font-family: 'Playfair Display', serif;">
                {{ $heroTitle ?: $siteName }}
            </h1>

            {{-- Gold decorative divider --}}
            <div class="flex items-center justify-center gap-3 mb-10">
                <span class="block w-12 h-px bg-amber-600/60"></span>
                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                <span class="block w-12 h-px bg-amber-600/60"></span>
            </div>

            @if($heroSubtitle)
                <p class="text-xl sm:text-2xl text-gray-300 font-light italic max-w-2xl mx-auto mb-14 leading-relaxed"
                   style="font-family: 'Playfair Display', serif;">
                    {{ $heroSubtitle }}
                </p>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
                <a href="{{ $heroCtaLink ?: route('menu') }}"
                   class="group px-10 py-4 bg-amber-700 hover:bg-amber-600 text-white text-[11px] sm:text-xs font-bold tracking-[0.25em] uppercase transition-all duration-300 shadow-2xl shadow-amber-900/40 hover:shadow-amber-700/50 flex items-center gap-3">
                    {{ $heroCtaText ?: 'Explore Our Menu' }}
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ route('menu') }}"
                   class="px-10 py-4 border border-white/25 hover:border-white/60 text-white/90 hover:text-white text-[11px] sm:text-xs font-bold tracking-[0.25em] uppercase transition-all duration-300 backdrop-blur-sm">
                    View All Dishes
                </a>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce">
            <span class="text-gray-500 text-[10px] tracking-[0.2em] uppercase">Scroll</span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════
        STORY — Elegant text-only statement
    ═══════════════════════════════════════════════════ --}}
    <section class="py-28 sm:py-36 bg-white">
        <div class="max-w-3xl mx-auto text-center px-6">
            <span class="text-amber-600 tracking-[0.3em] uppercase text-[10px] font-semibold mb-6 block">Our Philosophy</span>
            <h2 class="text-3xl sm:text-5xl text-gray-900 leading-tight mb-10" style="font-family: 'Playfair Display', serif;">
                Where Every Dish<br>Tells a Story
            </h2>
            <div class="flex items-center justify-center gap-3 mb-10">
                <span class="block w-8 h-px bg-amber-500/50"></span>
                <span class="block w-2 h-2 rounded-full bg-amber-500/50"></span>
                <span class="block w-8 h-px bg-amber-500/50"></span>
            </div>
            <p class="text-gray-500 text-lg sm:text-xl leading-[2] font-light">
                We believe in the power of authentic flavors. Our kitchen blends time-honored recipes with the freshest seasonal ingredients, creating dishes that are as beautiful as they are delicious. Every plate is a celebration of culinary artistry.
            </p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════
        CATEGORIES — Tall editorial-style cards
    ═══════════════════════════════════════════════════ --}}
    @if($featuredCategories->isNotEmpty())
        <section class="py-24 sm:py-28 bg-gray-950">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-amber-500 tracking-[0.3em] uppercase text-[10px] font-semibold mb-4 block">Discover</span>
                    <h2 class="text-4xl sm:text-5xl text-white" style="font-family: 'Playfair Display', serif;">Our Menu</h2>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach($featuredCategories as $category)
                        <a href="{{ route('menu', ['category' => $category->slug]) }}"
                           class="group relative block aspect-[3/4] overflow-hidden bg-gray-900 cursor-pointer">
                            {{-- Category Image --}}
                            <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 group-hover:scale-110 opacity-60 group-hover:opacity-40"
                                 style="background-image: url('{{ $category->image ? asset('storage/' . $category->image) : 'https://placehold.co/400x600/1c1917/d97706?text=' . urlencode($category->name) }}');"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>

                            {{-- Category label --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-end pb-10 px-4">
                                <div class="w-6 h-px bg-amber-500 mb-4 transition-all duration-500 group-hover:w-14"></div>
                                <h3 class="text-lg sm:text-xl text-white text-center tracking-wide" style="font-family: 'Playfair Display', serif;">
                                    {{ $category->name }}
                                </h3>
                                <span class="mt-3 text-[10px] text-amber-400/0 group-hover:text-amber-400 tracking-[0.2em] uppercase font-semibold transition-all duration-500 translate-y-2 group-hover:translate-y-0">
                                    Explore →
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════
        FEATURED DISHES — Clean editorial grid
    ═══════════════════════════════════════════════════ --}}
    @if($featuredProducts->isNotEmpty())
        <section class="py-28 sm:py-36 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <span class="text-amber-600 tracking-[0.3em] uppercase text-[10px] font-semibold mb-4 block">Chef's Selection</span>
                    <h2 class="text-4xl sm:text-5xl text-gray-900" style="font-family: 'Playfair Display', serif;">Signature Dishes</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-16">
                    @foreach($featuredProducts as $product)
                        <div class="group">
                            {{-- Product image --}}
                            @if($product->image_url)
                                <div class="relative w-full aspect-[4/3] overflow-hidden mb-8 bg-gray-100">
                                    <img src="{{ $product->image_url }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                                         loading="lazy">
                                    {{-- Hover overlay --}}
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-500"></div>
                                </div>
                            @endif

                            {{-- Product details --}}
                            <div class="text-center px-2">
                                <span class="text-amber-600/70 text-[10px] tracking-[0.2em] uppercase font-bold mb-2 block">
                                    {{ $product->category->name }}
                                </span>
                                <h3 class="text-xl sm:text-2xl text-gray-900 mb-3" style="font-family: 'Playfair Display', serif;">
                                    {{ $product->name }}
                                </h3>
                                @if($product->description)
                                    <p class="text-sm text-gray-400 font-light leading-relaxed mb-5 line-clamp-2 max-w-xs mx-auto">
                                        {{ $product->description }}
                                    </p>
                                @endif

                                {{-- Price & action --}}
                                <div class="flex items-center justify-center gap-6 pt-5 border-t border-gray-100">
                                    <span class="text-xl text-amber-700 font-medium" style="font-family: 'Playfair Display', serif;">
                                        {{ $currencySymbol }}{{ number_format($product->base_price, 2) }}
                                    </span>
                                    <a href="{{ route('menu') }}"
                                       class="text-[10px] tracking-[0.15em] uppercase font-bold text-gray-900 hover:text-amber-700 border-b border-gray-900 hover:border-amber-700 pb-0.5 transition-colors duration-300">
                                        Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-24 text-center">
                    <a href="{{ route('menu') }}"
                       class="inline-block px-14 py-5 bg-gray-950 hover:bg-black text-white text-[11px] font-bold tracking-[0.25em] uppercase transition-all duration-300 shadow-xl hover:shadow-2xl">
                        View Complete Menu
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════
        TESTIMONIAL / QUOTE — Dark cinematic strip
    ═══════════════════════════════════════════════════ --}}
    <section class="relative py-28 sm:py-36 bg-gray-950 overflow-hidden">
        {{-- Subtle background image --}}
        <div class="absolute inset-0 bg-cover bg-center opacity-15"
             style="background-image: url('{{ asset('storage/settings/hero-bg.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gray-950/80"></div>

        <div class="relative z-10 max-w-3xl mx-auto px-6 text-center">
            <svg class="w-10 h-10 text-amber-600/40 mx-auto mb-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
            <p class="text-2xl sm:text-3xl text-gray-200 font-light italic leading-relaxed mb-10"
               style="font-family: 'Playfair Display', serif;">
                The secret of success in life is to eat what you like and let the food fight it out inside.
            </p>
            <span class="text-amber-500 text-[11px] tracking-[0.3em] uppercase font-semibold">— Mark Twain</span>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════
        FINAL CTA — Order / Visit strip
    ═══════════════════════════════════════════════════ --}}
    <section class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <span class="text-amber-600 tracking-[0.3em] uppercase text-[10px] font-semibold mb-6 block">Ready?</span>
            <h2 class="text-3xl sm:text-5xl text-gray-900 mb-8" style="font-family: 'Playfair Display', serif;">
                Your Table Awaits
            </h2>
            <p class="text-gray-500 text-lg font-light mb-14 max-w-xl mx-auto leading-relaxed">
                Explore our full menu and order your favorites for pickup or delivery. Fresh, authentic, and prepared just for you.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
                <a href="{{ route('menu') }}"
                   class="px-12 py-5 bg-amber-700 hover:bg-amber-600 text-white text-[11px] font-bold tracking-[0.25em] uppercase transition-all duration-300 shadow-xl hover:shadow-amber-700/40">
                    Order Now
                </a>
                <a href="{{ route('page.show', 'contact-us') }}"
                   class="px-12 py-5 border border-gray-300 hover:border-gray-900 text-gray-900 text-[11px] font-bold tracking-[0.25em] uppercase transition-all duration-300">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
@endsection
