<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('CMS Pages Manager') }}
            </h2>
            <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                {{ __('+ Create Page') }}
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
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Title') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Slug / URL') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left tracking-wider">{{ __('Last Updated') }}</th>
                            <th class="px-6 py-3 text-right tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($pages as $page)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $page->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded">/page/{{ $page->slug }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($page->is_published)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Published') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ __('Draft') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    {{ $page->updated_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        {{ __('Edit') }}
                                    </a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this page?');">
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
                                    {{ __('No CMS pages found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($pages->hasPages())
                    <div class="p-4 border-t">
                        {{ $pages->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
