@php
    $delay = config('filament.livewire_loading_delay', 'default');
@endphp

<style>
    .bouncing-loader {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .bouncing-loader > div {
        width: 1rem; /* 16px */
        height: 1rem; /* 16px */
        margin: 3px 6px;
        border-radius: 50%;
        background-color: currentColor; /* Use text color for dots */
        animation: bouncing-loader 0.6s infinite alternate;
    }
    .bouncing-loader > div:nth-child(2) {
        animation-delay: 0.2s;
    }
    .bouncing-loader > div:nth-child(3) {
        animation-delay: 0.4s;
    }
    @keyframes bouncing-loader {
        to {
            opacity: 0.1;
            transform: translateY(-1rem);
        }
    }
</style>

<div
    wire:navigate.init
    class="fixed inset-0 z-50 hidden items-center justify-center"
    style="background-color: rgba(0, 0, 0, 0.4); display: none;"
>
        <div class="bouncing-loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div> 