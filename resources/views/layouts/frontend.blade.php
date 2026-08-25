<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $siteName . ($siteTagline ? ' — ' . $siteTagline : ''))</title>
    <meta name="description" content="@yield('meta_description', $siteTagline ?? $siteName)">
    @yield('seo_meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#FAFAFA] min-h-full flex flex-col selection:bg-amber-700 selection:text-white" x-data="{ mobileOpen: false }" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <!-- Top Announcement Bar (if opening hours or phone exist) -->
    @if($openingHours || $sitePhone)
        <div class="bg-gray-900 text-amber-200 text-xs py-2 px-4 border-b border-gray-800">
            <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
                <div class="flex items-center gap-4">
                    @if($openingHours)
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $openingHours }}</span>
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    @if($sitePhone)
                        <a href="tel:{{ $sitePhone }}" class="hover:text-white inline-flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>{{ $sitePhone }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-amber-100/80 shadow-sm transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between min-h-[5rem] py-2">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('home') }}" class="flex flex-col sm:flex-row items-start sm:items-center gap-0.5 sm:gap-3 group min-w-0">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-8 sm:h-10 w-auto object-contain">
                        @else
                            <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-tr from-amber-600 to-orange-500 flex items-center justify-center text-white shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                        @endif
                        <div class="flex flex-col min-w-0">
                            <span class="font-extrabold text-base sm:text-2xl text-gray-900 tracking-tight leading-tight group-hover:text-amber-600 transition-colors truncate max-w-[150px] sm:max-w-none">
                                {{ $siteName }}
                            </span>
                            @if($siteTagline)
                                <span class="text-xs text-amber-700/80 font-medium hidden sm:block">
                                    {{ $siteTagline }}
                                </span>
                            @endif
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                    @if($headerMenu && $headerMenu->items->isNotEmpty())
                        @foreach($headerMenu->items as $item)
                            <a href="{{ $item->resolved_url }}" target="{{ $item->target ?? '_self' }}" class="px-3.5 py-2 text-sm font-semibold text-gray-700 hover:text-amber-600 hover:bg-gray-50 rounded-lg transition">
                                {{ $item->label }}
                            </a>
                        @endforeach
                    @endif
                </nav>

                <!-- Actions: Cart & Auth -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                    <!-- Shopping Cart Button -->
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-500/20 transition-all duration-150 group">
                        <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span class="hidden sm:inline">Cart</span>
                        <span class="inline-flex items-center justify-center min-w-[1.35rem] h-5 px-1.5 text-xs font-bold font-mono text-amber-900 bg-amber-100 rounded-full">
                            {{ $cartCount }}
                        </span>
                    </a>

                    <!-- Auth Links -->
                    <div class="hidden sm:flex items-center gap-2">
                        @auth
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-xs font-bold uppercase tracking-wider text-amber-900 bg-amber-100 hover:bg-amber-200 rounded-lg transition">
                                    Admin
                                </a>
                            @else
                                <span class="text-sm font-medium text-gray-600">{{ auth()->user()->name }}</span>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 transition" title="Log Out">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-3.5 py-2 text-sm font-semibold text-gray-700 hover:text-amber-600 transition">
                                Log In
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile Menu Hamburger -->
                    <div class="flex items-center md:hidden">
                        <button type="button" @click="mobileOpen = !mobileOpen" class="p-2 rounded-lg text-gray-600 hover:text-amber-600 hover:bg-gray-100 focus:outline-none transition">
                            <svg class="w-6 h-6" x-show="!mobileOpen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            <svg class="w-6 h-6" x-show="mobileOpen" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden border-b border-gray-200 bg-white px-4 pt-2 pb-6 space-y-2 shadow-lg">
            @if($headerMenu && $headerMenu->items->isNotEmpty())
                @foreach($headerMenu->items as $item)
                    <a href="{{ $item->resolved_url }}" target="{{ $item->target ?? '_self' }}" class="block px-3 py-2.5 rounded-lg text-base font-semibold {{ request()->is(trim($item->resolved_url, '/')) || (request()->is('/') && $item->resolved_url == '/') ? 'text-amber-700 bg-amber-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        {{ $item->label }}
                    </a>
                @endforeach
            @endif

            <a href="{{ route('cart.index') }}" class="block px-3 py-2.5 rounded-lg text-base font-semibold {{ request()->routeIs('cart.index') ? 'text-amber-700 bg-amber-50' : 'text-gray-700 hover:bg-gray-50' }}">
                Shopping Cart ({{ $cartCount }})
            </a>

            @if($headerMenu && $headerMenu->items->isNotEmpty())
                @foreach($headerMenu->items as $item)
                    <a href="{{ $item->resolved_url }}" target="{{ $item->target ?? '_self' }}" class="block px-3 py-2.5 rounded-lg text-base font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $item->label }}
                    </a>
                @endforeach
            @endif

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-bold text-amber-900 bg-amber-100 rounded-lg">
                            Admin Dashboard
                        </a>
                    @else
                        <span class="text-sm font-medium text-gray-600">{{ auth()->user()->name }}</span>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-lg">
                            Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg">
                        Log In / Register
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Flash Messages Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between p-4 mb-4 text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between p-4 mb-4 text-red-800 bg-red-50 border border-red-200 rounded-xl shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" class="flex items-center justify-between p-4 mb-4 text-amber-800 bg-amber-50 border border-amber-200 rounded-xl shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-sm font-medium">{{ session('warning') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-700">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Dynamic Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Dynamic Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <!-- Column 1: Store Story / Tagline -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-8 w-auto">
                        @endif
                        <span class="text-xl font-black text-white tracking-tight">
                            {{ $siteName }}
                        </span>
                    </div>
                    @if($siteTagline)
                        <p class="text-sm text-gray-400 leading-relaxed">
                            {{ $siteTagline }}
                        </p>
                    @endif
                    @if($facebookUrl || $instagramUrl || $twitterUrl)
                        <div class="flex items-center gap-3 pt-2">
                            @if($facebookUrl)
                                <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-800 hover:bg-amber-600 text-gray-300 hover:text-white flex items-center justify-center transition">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            @endif
                            @if($instagramUrl)
                                <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-800 hover:bg-amber-600 text-gray-300 hover:text-white flex items-center justify-center transition">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            @endif
                            @if($twitterUrl)
                                <a href="{{ $twitterUrl }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-gray-800 hover:bg-amber-600 text-gray-300 hover:text-white flex items-center justify-center transition">
                                    <span class="sr-only">Twitter</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.936 9.936 0 0024 4.59z"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Column 2: Navigation / Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-wider uppercase mb-4">
                        Explore
                    </h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <a href="{{ route('cart.index') }}" class="hover:text-amber-400 transition">Shopping Cart</a>
                        </li>
                        @if($footerMenu && $footerMenu->items->isNotEmpty())
                            @foreach($footerMenu->items as $item)
                                <li>
                                    <a href="{{ $item->resolved_url }}" target="{{ $item->target ?? '_self' }}" class="hover:text-amber-400 transition">
                                        {{ $item->label }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <!-- Column 3: Contact Details -->
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-wider uppercase mb-4">
                        Contact Info
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-400">
                        @if($siteAddress)
                            <li class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $siteAddress }}</span>
                            </li>
                        @endif
                        @if($sitePhone)
                            <li class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <a href="tel:{{ $sitePhone }}" class="hover:text-amber-400 transition">{{ $sitePhone }}</a>
                            </li>
                        @endif
                        @if($siteEmail)
                            <li class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <a href="mailto:{{ $siteEmail }}" class="hover:text-amber-400 transition">{{ $siteEmail }}</a>
                            </li>
                        @endif
                    </ul>
                </div>

                <!-- Column 4: Opening Hours -->
                <div>
                    <h3 class="text-sm font-semibold text-white tracking-wider uppercase mb-4">
                        Opening Hours
                    </h3>
                    @if($openingHours)
                        <div class="p-4 bg-gray-800/80 rounded-xl border border-gray-700/60 text-sm text-amber-200">
                            <p class="font-medium leading-relaxed">{{ $openingHours }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">Open 7 days a week for pickup & delivery.</p>
                    @endif
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="mt-12 pt-8 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <p>
                    {!! $copyrightText ?: ('&copy; ' . date('Y') . ' ' . e($siteName) . '. All rights reserved.') !!}
                </p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="hover:text-gray-400 transition">Storefront</a>
                    <span>&bull;</span>
                    <a href="{{ route('menu') }}" class="hover:text-gray-400 transition">Menu</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
