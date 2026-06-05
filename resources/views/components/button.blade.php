@props(['color' => 'blue'])

@php
    $baseClasses = 'inline-block px-5 py-3 rounded-[10px] text-base font-semibold
                    cursor-pointer transition-all duration-300 ease-in-out
                    hover:-translate-y-0.5 hover:bg-gradient-to-r';
    
    $colorClasses = match($color) {
        'red' => 'text-white border-none shadow-[0_4px_15px_rgba(231,76,60,0.4)] bg-gradient-to-br from-[#c2433a] to-[#EB4C42]
                    hover:shadow-[0_6px_20px_rgba(231,76,60,0.6)]',
        'gray' => 'border border-gray-300 text-gray-700 hover:bg-gray-50',
        default => 'text-white border-none shadow-[0_4px_15px_rgba(37,117,252,0.4)] bg-gradient-to-br from-[#1A2A6C] to-[#3456DB]
                    hover:shadow-[0_6px_20px_rgba(37,117,252,0.6)] hover:from-[#1A2A6C] hover:to-[#3456DB]',
    };
@endphp

<button {{ $attributes->merge(['class' => $baseClasses . ' ' . $colorClasses]) }}>
    {{ $slot }}
</button>