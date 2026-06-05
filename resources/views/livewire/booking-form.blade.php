<div class="min-h-screen bg-[#f8f9fc] font-sans py-10 px-4">
    <div class="max-w-[800px] mx-auto bg-white rounded-[15px] shadow-[0_0_30px_rgba(0,0,0,0.1)] p-6 md:p-10">

        @if($submitted)
            <div class="text-center py-12">
                <div class="text-7xl mb-6">✅</div>
                <h2 class="text-2xl font-bold text-green-600 mb-4">Мы получили вашу заявку!</h2>
                <p class="text-gray-600 mb-8">Скоро мы её рассмотрим. Ожидайте уведомления.</p>
                <x-button wire:click="resetForm">Подать новую заявку</x-button>
            </div>
        @else

        <div class="text-center mb-4 border-b-[3px] border-primary pb-5">
            <h1 class="text-[#1a2a6c] font-bold text-3xl md:text-4xl mb-2">Бронирование аудитории</h1>
            <p class="text-secondary text-[1.1rem]">Заполните форму, отправьте заявку и мы её рассмотрим</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 rounded-[10px] p-4 mb-4">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-700 rounded-[10px] p-4 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8">
            <button wire:click="openSettingsModal" type="button"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100
                    transition-colors font-semibold cursor-pointer">
                ⚙️ Настройки
            </button>

            <a href="{{ route('schedule') }}"
                class="inline-block px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100
                        transition-colors font-semibold">
                📅 Расписание
            </a>
        </div>

        <form wire:submit.prevent="submit">

            <div class="mb-4">
                <label class="block font-semibold text-[#495057] mb-2">
                    Аудитория
                    <span class="text-[#D5473E]">*</span>
                </label>
                <x-select wire:model="classroom_id" required>
                    <option value="">Выберите аудиторию</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}">
                            {{ $classroom->room }} ({{ $classroom->category->category }})
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label class="block font-semibold text-[#495057] mb-2">
                        Дата бронирования
                        <span class="text-[#D5473E]">*</span>
                    </label>

                    <div wire:ignore>
                        <x-input wire:model="date" id="event_date" placeholder="ДД.ММ.ГГГГ" required/>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-[#495057] mb-2">
                        Время бронирования
                        <span class="text-[#D5473E]">*</span>
                    </label>

                    <div class="flex flex-col md:flex-row gap-5 items-center">
                        <x-input wire:model="start_time" id="start_time" placeholder="14:30" required/>
                        <span class="text-xl font-bold text-secondary">—</span>
                        <x-input wire:model="end_time" id="end_time" placeholder="16:00" required/>
                    </div>
                </div>
            </div>

            @if(count($busySlots))
                <div class="bg-blue-100 text-blue-800 rounded-[10px] p-4 mb-4">
                    <b>⛔ Занятые слоты:</b>
                    <div class="mt-2 space-y-1">
                        @foreach($busySlots as $slot)
                            <div>
                                {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                {{ $slot['status'] === 'pending' ? '(на рассмотрении)' : '(занято)' }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <label class="block font-semibold text-[#495057] mb-2">
                    Цель бронирования
                    <span class="text-[#D5473E]">*</span>
                </label>
                <x-input wire:model="purpose" type="textarea" placeholder="Собрание студсовета, репетиция, занятие..." rows="3" required/>
            </div>

            <div class="bg-[#f8f9fa] p-5 rounded-[10px] mb-4">
                <h5 class="font-[600] text-lg mb-4">🔧 Оборудование</h5>
                <div class="mb-4">
                    <label class="block font-semibold text-[#495057] mb-2">Необходимое оборудование</label>
                    <x-input wire:model="equipment" type="textarea" placeholder="Проектор, микрофоны, стулья..." rows="2"/>
                    <small class="text-secondary text-sm">Оставьте пустым, если оборудование не требуется</small>
                </div>

                <div class="bg-[#f8f9fa] rounded-[10px]">
                    <label class="block font-semibold text-[#495057] mb-3">Нужен ли технический специалист?</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                wire:model="is_tech_support" type="radio" value="1"
                                class="w-4 h-4 text-primary accent-[#1a2a6c] border-gray-300 focus:ring-primary">
                            <span>Да</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                wire:model="is_tech_support" type="radio" value="0"
                                class="w-4 h-4 text-primary accent-[#1a2a6c] border-gray-300 focus:ring-primary">
                            <span>Нет</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block font-semibold text-[#495057] mb-2">Комментарий для администратора</label>
                <x-input wire:model="user_comment" type="textarea" placeholder="Дополнительная информация, пожелания, особенности мероприятия..." rows="3"/>
            </div>

            <div class="bg-[#FFF2CE] text-[#684F06] rounded-[10px] p-5 mb-4">
                <h5 class="font-bold text-lg mb-3">📋 Правила бронирования</h5>
                <ul class="space-y-1 list-disc pl-5">
                    <li>Бронирование возможно только минимум за 24 часа до мероприятия</li>
                    <li>При использовании танцевального зала обязательна сменная обувь</li>
                    <li>Необходимо поддерживать чистоту после мероприятия</li>
                    <li>Мебель должна быть возвращена на свои места</li>
                    <li>Заявка будет рассмотрена администратором в течение 24 часов</li>
                </ul>
            </div>

            <div class="flex flex-col md:flex-row gap-3 md:justify-end">
                <x-button type="submit" wire:loading.attr="disabled">Отправить заявку</x-button>
            </div>
        </form>
        @endif
        
        <div wire:loading.flex class="fixed inset-0 bg-[rgba(255,255,255,0.7)] z-[9999] flex-col justify-center items-center">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-primary font-semibold">Загрузка...</p>
        </div>

        <div class="text-center mt-[30px] pt-5 mb-8 border-t border-[#e3e6f0] text-secondary text-[0.9rem]">
            <p>По вопросам обращайтесь: 8 (3412) 77-60-55, доб. 1371</p>
            <p class="mt-2">
                Также вы можете подать заявку через
                <a href="https://vk.com" target="_blank" class="text-primary hover:underline">VK бота</a>
            </p>
        </div>

        <x-filter :classrooms="$classrooms"/>

        @if($loadError)
            <div class="bg-red-100 border border-red-300 text-red-700 rounded-[10px] p-4 mb-6">
                ❌ Не удалось загрузить данные. Попробуйте позже.
            </div>
        @endif

        <div class="mt-10">
            <h2 class="text-2xl font-bold mb-6 text-center">📋 Мои заявки</h2>

            @forelse($bookings as $booking)
                <div class="bg-white rounded-xl shadow p-4 mb-4 border border-[#e3e6f0]">
                    <div class="flex flex-col md:flex-row md:justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                ID: {{ $booking->id }}
                            </span>
                        </div>

                        <span
                            class="text-sm font-semibold

                            @if($booking->status === 'pending')
                                text-yellow-600
                            @elseif($booking->status === 'approved')
                                text-green-600
                            @elseif($booking->status === 'rejected' || $booking->status === 'cancelled')
                                text-red-600
                            @endif">

                            {{ match($booking->status) {
                                'pending' => '⏳ Ожидает',
                                'approved' => '✅ Одобрена',
                                'rejected' => '❌ Отклонена',
                                'cancelled' => '❌ Отменена',
                            } }}
                        </span>
                    </div>

                    <div class="font-semibold">Аудитория {{ $booking->classroom->room }}</div>

                    <div class="text-sm text-gray-600 mt-2">
                        📅 {{ $booking->date }}
                        |
                        ⏰ {{ $booking->start_time }}
                        -
                        {{ $booking->end_time }}
                    </div>

                    <div class="mt-3"><b>Цель:</b> {{ $booking->purpose }}</div>

                    @if($booking->user_comment)
                        <div class="text-gray-500 text-sm mt-2">{{ $booking->user_comment }}</div>
                    @endif

                    @if($booking->admin_comment)
                        <div class="text-gray-500 text-sm mt-2">
                            <b>Комментарий администратора:</b>
                            {{ $booking->admin_comment }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-gray-500 text-center">Заявок не найдено</div>
            @endforelse

            @if($hasMoreBookings)
                <div class="flex justify-center mt-6">
                    <x-button wire:click="loadMore">Показать ещё</x-button>
                </div>
            @endif
        </div>

        @if($showSettingsModal)
            <x-modal title="Настройки">
                <label class="block font-semibold text-[#495057] mb-2">Привязать страницу ВКонтакте для уведомлений</label>
                <x-input wire:model="settingsVkLink" placeholder="https://vk.com/username или @username"/>

                @if($settingsVkLink)
                    <small class="inline-block text-sm text-green-600">Страница привязана</small>
                @endif

                <label class="block font-semibold text-[#495057] mb-2 mt-4">
                    Номер телефона <span class="text-danger">*</span>
                </label>
                <x-input wire:model="settingsPhone" placeholder="+7 (999) 123-45-67"/>
                @error('settingsPhone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <small class="text-gray-500 text-sm block mb-6 mt-1">
                    Укажите номер телефона один раз – он будет обязателен для всех ваших заявок.
                </small>

                <x-slot name="footer">
                    <x-button wire:click="saveSettings">Сохранить</x-button>
                    <x-button wire:click="closeSettingsModal" color="gray">Отмена</x-button>
                </x-slot>
            </x-modal>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            flatpickr("#event_date", {
                dateFormat: "d.m.Y",
                locale: "ru",
                minDate: "today",

                onChange: function(selectedDates, dateStr) {
                    @this.set('date', dateStr);
                }
            });

            function setupTimeMask(element) {
                element.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');

                    if (value.length >= 3) {
                        value =
                            value.substring(0, 2)
                            + ':'
                            + value.substring(2, 4);
                    } else if (value.length >= 1) {
                        if (parseInt(value) > 23) {
                            value = '23';
                        }
                    }

                    e.target.value = value.substring(0, 5);
                });
            }

            setupTimeMask(
                document.getElementById('start_time')
            );

            setupTimeMask(
                document.getElementById('end_time')
            );
        });
    </script>
</div>