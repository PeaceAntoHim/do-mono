@props([
    'class' => 'h-5 w-5 text-primary-500',
])

<div
    @class([
        'animate-spin rounded-full border-2 border-current border-t-transparent',
        $class,
    ])
    role="status"
    wire:loading.delay
></div> 