@extends('layouts.frontend')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . $siteName)
@section('meta_description', $page->meta_description ?: 'Discover the story behind ' . $siteName)

@section('content')
    <!-- Elegant Page Hero -->
    <section class="relative py-28 sm:py-36 bg-gray-950 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('{{ asset('storage/settings/hero-bg.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-gray-950/60 to-gray-950"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <span class="text-amber-500 font-medium tracking-[0.3em] uppercase text-xs mb-6 block">Our Heritage</span>
            <h1 class="text-5xl sm:text-6xl lg:text-7xl text-white mb-6" style="font-family: 'Playfair Display', serif;">
                {{ $page->title }}
            </h1>
            <div class="w-16 h-[2px] bg-amber-600 mx-auto"></div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-20 sm:py-28 bg-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Image Column -->
                <div class="relative">
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ asset('storage/settings/hero-bg.jpg') }}" alt="Our Restaurant" class="w-full h-full object-cover">
                    </div>
                    <!-- Gold accent border -->
                    <div class="absolute -bottom-4 -right-4 w-full h-full border-2 border-amber-600/30 -z-10"></div>
                </div>

                <!-- Text Column -->
                <div>
                    <span class="text-amber-600 font-semibold tracking-[0.2em] uppercase text-xs mb-4 block">Est. 1998</span>
                    <h2 class="text-3xl sm:text-4xl text-gray-900 mb-8 leading-tight" style="font-family: 'Playfair Display', serif;">
                        A Passion for Authentic Italian Cuisine
                    </h2>
                    <div class="w-12 h-[2px] bg-amber-600 mb-8"></div>
                    <div class="text-gray-600 text-base leading-[1.9] space-y-6 font-light">
                        <p>Welcome to <strong class="text-gray-900 font-medium">{{ $siteName }}</strong>. Founded in 1998, we have been serving the finest, authentic Italian cuisine to our beloved community for over two decades.</p>
                        <p>Our recipes have been passed down through generations, ensuring every bite is a taste of tradition. We source only the freshest local ingredients and import the finest cheeses and olive oils directly from Italy.</p>
                        <p>Whether you are craving a classic Margherita pizza fresh from our wood-fired oven or a rich, creamy Fettuccine Alfredo, we promise an unforgettable dining experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values / Pillars Section -->
    <section class="py-20 sm:py-28 bg-[#FAFAFA] border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center">
            <span class="text-amber-600 font-semibold tracking-[0.2em] uppercase text-xs mb-4 block">What We Stand For</span>
            <h2 class="text-3xl sm:text-4xl text-gray-900 mb-16" style="font-family: 'Playfair Display', serif;">Our Philosophy</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Pillar 1 -->
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full border border-amber-600/40 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg>
                    </div>
                    <h3 class="text-lg text-gray-900 font-semibold mb-3" style="font-family: 'Playfair Display', serif;">Authentic Recipes</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed max-w-xs">Handed down through generations of Italian cooking tradition, our recipes are the heart and soul of every dish we serve.</p>
                </div>

                <!-- Pillar 2 -->
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full border border-amber-600/40 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"></path></svg>
                    </div>
                    <h3 class="text-lg text-gray-900 font-semibold mb-3" style="font-family: 'Playfair Display', serif;">Finest Ingredients</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed max-w-xs">We source locally and import the finest cheeses, olive oils, and spices directly from Italy to guarantee uncompromising quality.</p>
                </div>

                <!-- Pillar 3 -->
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full border border-amber-600/40 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.502-4.688-4.502-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.748 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path></svg>
                    </div>
                    <h3 class="text-lg text-gray-900 font-semibold mb-3" style="font-family: 'Playfair Display', serif;">Made with Love</h3>
                    <p class="text-gray-500 text-sm font-light leading-relaxed max-w-xs">Every plate is prepared with passion and care. Come for the food, stay for the warm, family atmosphere. <em>Buon Appetito!</em></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-24 bg-gray-900 overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-6 text-center">
            <h2 class="text-3xl sm:text-4xl text-white mb-6" style="font-family: 'Playfair Display', serif;">Ready to Experience the Taste?</h2>
            <p class="text-gray-400 font-light mb-10">Explore our menu and order your favorites for pickup or delivery.</p>
            <a href="{{ route('menu') }}" class="inline-block px-12 py-4 border border-amber-600 text-amber-500 hover:bg-amber-600 hover:text-white text-xs font-bold tracking-[0.2em] uppercase transition-all duration-300">
                View Our Menu
            </a>
        </div>
    </section>
@endsection
