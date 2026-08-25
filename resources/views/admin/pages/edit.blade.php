<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Page: ') }} {{ $page->title }}
            </h2>
            <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                {{ __('&larr; Back to Pages') }}
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

            <form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="title" :value="__('Page Title')" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $page->title)" required />
                </div>

                <div>
                    <x-input-label for="slug" :value="__('Slug / URL Path')" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $page->slug)" required />
                </div>

                <div>
                    <x-input-label for="content" :value="__('Page Content (HTML supported)')" />
                    <textarea id="content" name="content" rows="12" class="mt-1 block w-full font-mono text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('content', $page->content) }}</textarea>
                </div>

                <div class="border-t pt-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">{{ __('SEO Meta Information') }}</h4>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="meta_title" :value="__('Meta Title')" />
                            <x-text-input id="meta_title" name="meta_title" type="text" class="mt-1 block w-full" :value="old('meta_title', $page->meta_title)" />
                        </div>

                        <div>
                            <x-input-label for="meta_description" :value="__('Meta Description')" />
                            <textarea id="meta_description" name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>

                        <div>
                            <x-input-label for="og_image" :value="__('Social Share Image (OG Image)')" />
                            <input id="og_image" name="og_image" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @if($page->og_image)
                                <div class="mt-2 flex items-center space-x-2">
                                    <img src="{{ asset('storage/' . $page->og_image) }}" alt="OG Image" class="h-12 w-auto rounded border" />
                                    <span class="text-xs text-gray-500">{{ __('Current OG Image') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                        <span class="ms-2 text-sm font-medium text-gray-700">{{ __('Published / Active') }}</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold uppercase text-gray-700 hover:bg-gray-200">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Update Page') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
