<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
                
                @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'editor']))
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ url('/') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ __('Access Admin Panel') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
