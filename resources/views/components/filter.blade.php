@props([
    'classrooms',
    'dateProp' => 'filterDate',
    'classroomProp' => 'filterClassroom',
    'showStatus' => true,
    'statusProp' => 'filterStatus',
    'resetAction' => 'resetFilters'
])

<div class="bg-white rounded-[10px] shadow-sm border border-[#e3e6f0] p-5 mb-6">
    <div class="flex flex-wrap gap-4 items-end">
        <div class="flex flex-col flex-1 min-w-[200px]">
            <label class="mb-2 font-semibold text-[#1a2a6c]">Дата</label>
            <div wire:ignore>
                <input type="text" id="date_filter_input" placeholder="ДД.ММ.ГГГГ"
                       class="w-full px-4 py-[12px] border-2 border-[#e0e0e0] rounded-[10px]
                       focus:outline-none focus:border-[#3456db] font-medium transition-all duration-300
                       bg-white focus:shadow-[0_0_0_4px_rgba(52,86,219,0.12)] cursor-pointer">
            </div>
        </div>

        <div class="flex flex-col flex-1 min-w-[200px]">
            <label class="mb-2 font-semibold text-[#1a2a6c]">Аудитория</label>
            <x-select wire:model="{{ $classroomProp }}">
                <option value="">Все аудитории</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}">{{ $classroom->room }}</option>
                @endforeach
            </x-select>
        </div>

        @if($showStatus)
            <div class="flex flex-col flex-1 min-w-[200px]">
                <label class="mb-2 font-semibold text-[#1a2a6c]">Статус заявки</label>
                <x-select wire:model="{{ $statusProp }}">
                    <option value="">Все статусы</option>
                    <option value="pending">⏳ В ожидании</option>
                    <option value="approved">✅ Одобрена</option>
                    <option value="rejected">❌ Отклонена</option>
                    <option value="cancelled">🚫 Отменена</option>
                </x-select>
            </div>
        @endif

        <div class="flex-shrink-0">
            <x-button wire:click="{{ $resetAction }}" color="red">Сбросить</x-button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            const fp = flatpickr("#date_filter_input", {
                dateFormat: "d.m.Y",
                locale: "ru",
                onChange: function(selectedDates, dateStr) {
                    @this.set('{{ $dateProp }}', dateStr);
                }
            });

            Livewire.on('resetFilterDate', () => {
                if (fp) fp.clear();
            });
        });
    </script>
</div>