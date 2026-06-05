@props(['type' => 'input'])

@if ($type === 'textarea')
    <textarea {{ $attributes->merge([
        'class' => 'w-full p-3 border-2 border-[#e3e6f0] rounded-[8px] text-base transition-all duration-300 bg-white
                    focus:outline-none outline-none focus:border-primary focus:ring-4 focus:ring-[rgba(78,115,223,0.25)]
                    min-h-[80px]'
    ]) }}>{{ $slot }}</textarea>
@else
    <input {{ $attributes->merge([
        'class' => 'w-full p-3 border-2 border-[#e3e6f0] rounded-[8px] text-base transition-all duration-300 bg-white
                    focus:outline-none outline-none focus:border-primary focus:ring-4 focus:ring-[rgba(78,115,223,0.25)]'
    ]) }} />
@endif