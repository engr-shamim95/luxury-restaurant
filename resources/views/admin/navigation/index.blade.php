<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Navigation Menus & Links') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Add Navigation Item Form -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Add Item Form -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">
                            {{ __('+ Add Navigation Link') }}
                        </h3>

                        <form action="{{ route('admin.navigation.items.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="navigation_menu_id" :value="__('Select Menu Location')" />
                                <select id="navigation_menu_id" name="navigation_menu_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach($menus as $menu)
                                        <option value="{{ $menu->id }}">{{ $menu->name }} ({{ $menu->location }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="label" :value="__('Link Label')" />
                                <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" :value="old('label')" placeholder="e.g. Catering, Menu, Contact" required />
                            </div>

                            <div>
                                <x-input-label for="link_type" :value="__('Link Destination Type')" />
                                <div class="mt-2 space-y-2" x-data="{ linkType: 'page' }">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="link_type_choice" value="page" x-model="linkType" class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-700">{{ __('Link to CMS Page') }}</span>
                                    </label>
                                    <label class="inline-flex items-center ms-4">
                                        <input type="radio" name="link_type_choice" value="url" x-model="linkType" class="text-indigo-600 focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-700">{{ __('Custom URL / Path') }}</span>
                                    </label>

                                    <!-- Select Page -->
                                    <div x-show="linkType === 'page'" class="pt-2">
                                        <x-input-label for="page_id" :value="__('Select Published Page')" />
                                        <select id="page_id" name="page_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">{{ __('-- Choose Page --') }}</option>
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}">{{ $page->title }} (/page/{{ $page->slug }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Custom URL -->
                                    <div x-show="linkType === 'url'" class="pt-2">
                                        <x-input-label for="url" :value="__('Custom URL or Route Path')" />
                                        <x-text-input id="url" name="url" type="text" class="mt-1 block w-full" placeholder="e.g. /menu or https://example.com" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="order" :value="__('Sort Order')" />
                                    <x-text-input id="order" name="order" type="number" class="mt-1 block w-full" :value="old('order', 0)" min="0" />
                                </div>
                                <div>
                                    <x-input-label for="target" :value="__('Target Window')" />
                                    <select id="target" name="target" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="_self">{{ __('Same Window (_self)') }}</option>
                                        <option value="_blank">{{ __('New Window (_blank)') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-2">
                                <x-primary-button class="w-full justify-center">
                                    {{ __('Add Link to Menu') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Create Menu Container Form -->
                    <div class="bg-white shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3 border-b pb-2">
                            {{ __('+ Create New Menu Group') }}
                        </h3>
                        <form action="{{ route('admin.navigation.menus.store') }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="menu_name" :value="__('Menu Name')" />
                                <x-text-input id="menu_name" name="name" type="text" class="mt-1 block w-full text-sm" placeholder="e.g. Mobile Sidebar" required />
                            </div>
                            <div>
                                <x-input-label for="location" :value="__('Location Slug (unique)')" />
                                <x-text-input id="location" name="location" type="text" class="mt-1 block w-full text-sm" placeholder="e.g. sidebar" required />
                            </div>
                            <button type="submit" class="w-full py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-semibold uppercase">
                                {{ __('Create Menu Group') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: List of Menus and Ordered Items -->
                <div class="lg:col-span-2 space-y-6">
                    @forelse($menus as $menu)
                        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-base">{{ $menu->name }}</h3>
                                    <span class="text-xs text-gray-500">{{ __('Location key:') }} <code class="bg-gray-200 px-1 py-0.5 rounded">{{ $menu->location }}</code></span>
                                </div>
                                <span class="text-xs bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded-full font-semibold">
                                    {{ $menu->items->count() }} {{ __('items') }}
                                </span>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @forelse($menu->items as $item)
                                    <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded text-gray-600">#{{ $item->order }}</span>
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $item->label }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if($item->page)
                                                        <span class="text-emerald-600 font-medium">📄 Page: {{ $item->page->title }}</span> (/page/{{ $item->page->slug }})
                                                    @else
                                                        <span class="text-blue-600 font-medium">🔗 URL:</span> {{ $item->url }}
                                                    @endif
                                                    @if($item->target === '_blank')
                                                        <span class="text-gray-400 ms-1">(opens in new tab)</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <!-- Delete Item Button -->
                                            <form action="{{ route('admin.navigation.items.destroy', $item) }}" method="POST" onsubmit="return confirm('Remove this link from menu?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium px-2 py-1 rounded hover:bg-red-50">
                                                    {{ __('Remove') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-sm text-gray-500">
                                        {{ __('No items in this menu yet. Use the form on the left to add items.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-8 text-center text-gray-500 rounded-lg shadow-sm">
                            {{ __('No navigation menus created.') }}
                        </div>
                    @endforelse
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
