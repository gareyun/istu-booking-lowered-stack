@props(['booking', 'isTech' => false])

<div class="bg-white rounded-[10px] shadow-[0_4px_15px_rgba(0,0,0,0.1)] p-5 transition-all duration-300
            hover:-translate-y-[5px] hover:shadow-[0_8px_25px_rgba(0,0,0,0.15)] flex flex-col justify-between">
    <div>
        <div class="flex justify-between items-start mb-[15px]">
            <div class="text-[0.9rem] text-[#7f8c8d]">ID: {{ $booking->id }}
                @if(!$booking->user_id && $booking->vk_user_id)
                    <span class="text-xs text-gray-400 mt-1">(через VK бота)</span>
                @endif
            </div>

            @if($isTech)
                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    Одобрена
                </span>
            @endif
        </div>

        <div class="text-[1.3rem] font-bold text-[#1a2a6c] mb-[15px]">
            Аудитория: {{ $booking->classroom->room }}
        </div>

        <div class="space-y-[8px]">
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">ФИО: </div>
                <div class="text-right">{{ optional($booking->user)->name ?? $booking->name ?? '—' }}</div>
            </div>
            
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">Факультет: </div>
                <div class="text-right">{{ optional($booking->user)->faculty ?? $booking->faculty ?? '—' }}</div>
            </div>
            
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">Группа: </div>
                <div class="text-right">{{ optional($booking->user)->group ?? $booking->group ?? '—' }}</div>
            </div>
            
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">Номер телефона: </div>
                <div class="text-right">{{ optional($booking->user)->phone ?? $booking->phone ?? '—' }}</div>
            </div>
            
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">Дата: </div>
                <div class="text-right">{{ $booking->date }}</div>
            </div>
            
            <div class="flex justify-between">
                <div class="font-bold text-[#7f8c8d]">Время: </div>
                <div class="text-right">{{ $booking->start_time }} - {{ $booking->end_time }}</div>
            </div>
            
            <div class="flex justify-between gap-5">
                <div class="font-bold text-[#7f8c8d]">Цель: </div>
                <div class="text-right">{{ $booking->purpose }}</div>
            </div>
            
            <div class="flex justify-between gap-5">
                <div class="font-bold text-[#7f8c8d]">Оборудование: </div>
                <div class="text-right {{ $isTech && $booking->equipment ? 'font-semibold text-blue-700' : '' }}">
                    {{ $booking->equipment ?: 'Нет' }}
                </div>
            </div>
            
            <div class="flex justify-between gap-5">
                <div class="font-bold text-[#7f8c8d]">Тех. специалист: </div>
                <div class="text-right {{ $isTech && $booking->is_tech_support ? 'font-semibold text-blue-700' : '' }}">
                    {{ $booking->is_tech_support ? 'Да' : 'Нет' }}
                </div>
            </div>

            @if($booking->user_comment)
                <div class="flex justify-between gap-5">
                    <div class="font-bold text-[#7f8c8d]">Комментарий студента: </div>
                    <div class="text-right break-words">{{ $booking->user_comment }}</div>
                </div>
            @endif
            
            @if($booking->admin_comment)
                <div class="flex justify-between gap-5">
                    <div class="font-bold text-[#7f8c8d]">Комментарий администратора: </div>
                    <div class="text-right break-words">{{ $booking->admin_comment }}</div>
                </div>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="mt-5 border-t border-gray-100 pt-4">
            {{ $actions }}
        </div>
    @endif
</div>