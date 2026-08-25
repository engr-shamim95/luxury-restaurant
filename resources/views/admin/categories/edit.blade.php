<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Category: ') }} {{ $category->name }}
            </h2>
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                {{ __('&larr; Back to Categories') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Category Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name)" required />
                </div>

                <div>
                    <x-input-label for="slug" :value="__('URL Slug')" />
                    <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $category->slug)" required />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $category->description) }}</textarea>
                </div>

                <div>
                    <x-input-label for="image" :value="__('Category Banner / Image')" />
                    <input id="image" name="image" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    @if($category->image)
                        <div class="mt-2 flex items-center space-x-2">
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="h-12 w-12 object-cover rounded border" />
                            <span class="text-xs text-gray-500">{{ __('Current Image') }}</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-6 items-center border-t pt-4">
                    <div>
                        <x-input-label for="order" :value="__('Sort Order')" />
                        <x-text-input id="order" name="order" type="number" class="mt-1 block w-full" :value="old('order', $category->order)" min="0" />
                    </div>

                    <div class="pt-5">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <span class="ms-2 text-sm font-medium text-gray-700">{{ __('Category is Active & Visible') }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-xs font-semibold uppercase text-gray-700 hover:bg-gray-200">
                        {{ __('Cancel') }}
                    </a>
                    <x-primary-button>
                        {{ __('Update Category') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
