<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Manager (Zero Terminal)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-medium mb-4">Run Artisan Commands</h3>
                    <p class="mb-6 text-sm text-gray-500">Execute system commands without SSH or Terminal access. Use these carefully.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @php
                            $commands = [
                                'cache:clear' => 'Clear Application Cache',
                                'config:clear' => 'Clear Configuration Cache',
                                'route:clear' => 'Clear Route Cache',
                                'view:clear' => 'Clear Compiled Views',
                                'optimize:clear' => 'Clear All Caches (Optimize)',
                                'storage:link' => 'Create Storage Link'
                            ];
                        @endphp

                        @foreach($commands as $cmd => $label)
                        <form action="{{ route('admin.system.run') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="command" value="{{ $cmd }}">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition">
                                {{ $label }} <br><span class="text-xs font-normal text-blue-200">{{ $cmd }}</span>
                            </button>
                        </form>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
