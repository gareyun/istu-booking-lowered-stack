@props(['wrapperClass' => ''])

<div @class(['relative w-full', $wrapperClass])>
    <select {{ $attributes->merge([
        'class' => 'w-full appearance-none px-[15px] py-[12px] pr-10 border-2 border-gray-300 rounded-[10px]
                    bg-white text-[#333] text-base transition-all duration-300 focus:outline-none focus:border-[#3456db]
                    focus:ring-4 focus:ring-[rgba(52,86,219,0.15)] cursor-pointer'
    ]) }}>
        {{ $slot }}
    </select>

    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
</div>