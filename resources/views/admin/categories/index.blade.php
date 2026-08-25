<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Menu Categories') }}
            </h2>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('+ Add Category') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Sort') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Category') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Products') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-500">
                                    #{{ $category->order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($category->image)
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-10 h-10 object-cover rounded-md me-3 border" />
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-gray-100 flex items-center justify-center me-3 text-gray-400 font-bold border">
                                                📂
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $category->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700">
                                        {{ $category->products_count }} {{ __('dishes') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($category->is_active)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category? All associated products will also be deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    {{ __('No categories found. Click "+ Add Category" to create one.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($categories->hasPages())
                    <div class="p-4 border-t">
                        {{ $categories->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
