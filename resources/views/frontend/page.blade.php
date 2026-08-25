@extends('layouts.frontend')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . $siteName)
@section('meta_description', $page->meta_description ?: $page->title)

@section('content')
    <!-- Page Hero -->
    <section class="relative py-24 sm:py-32 bg-gray-950 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ asset('storage/settings/hero-bg.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-gray-950/60 to-gray-950"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl text-white mb-6" style="font-family: 'Playfair Display', serif;">
                {{ $page->title }}
            </h1>
            <div class="w-12 h-[2px] bg-amber-600 mx-auto"></div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="py-16 sm:py-24 bg-white">
        <div class="max-w-3xl mx-auto px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-12">
                <a href="{{ route('home') }}" class="hover:text-amber-600 transition">Home</a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-600">{{ $page->title }}</span>
            </nav>

            <!-- CMS Rich Text Content -->
            <div class="prose prose-lg max-w-none text-gray-600 font-light leading-[1.9]
                        prose-headings:font-normal prose-headings:text-gray-900
                        prose-h2:text-2xl prose-h2:tracking-tight prose-h2:mb-6 prose-h2:mt-12
                        prose-a:text-amber-700 prose-a:no-underline hover:prose-a:underline
                        prose-strong:text-gray-800 prose-strong:font-medium
                        prose-li:marker:text-amber-600"
                 style="font-family: 'Plus Jakarta Sans', sans-serif;">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endsection
