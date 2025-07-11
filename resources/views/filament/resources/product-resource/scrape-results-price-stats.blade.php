{{-- Standalone price statistics widget --}}
<div class="mb-8">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="fi-section rounded-xl bg-white shadow-sm dark:bg-gray-800 dark:ring-white/10 p-6">
            <div class="flex flex-col gap-y-1">
                <h3 class="text-base font-semibold leading-6 text-gray-700 dark:text-gray-200">Average Price</h3>
                <p class="text-2xl font-semibold tracking-tight text-primary-600 dark:text-primary-400">
                    {{ $avgPrice }}
                </p>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm dark:bg-gray-800 dark:ring-white/10 p-6">
            <div class="flex flex-col gap-y-1">
                <h3 class="text-base font-semibold leading-6 text-gray-700 dark:text-gray-200">Minimum Price</h3>
                <p class="text-2xl font-semibold tracking-tight text-green-600 dark:text-green-400">
                    {{ $minPrice }}
                </p>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm dark:bg-gray-800 dark:ring-white/10 p-6">
            <div class="flex flex-col gap-y-1">
                <h3 class="text-base font-semibold leading-6 text-gray-700 dark:text-gray-200">Maximum Price</h3>
                <p class="text-2xl font-semibold tracking-tight text-orange-600 dark:text-orange-400">
                    {{ $maxPrice }}
                </p>
            </div>
        </div>
    </div>
</div> 