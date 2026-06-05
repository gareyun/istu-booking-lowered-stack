<div class="min-h-screen bg-[#f5f7fa] text-[#333] font-['Segoe_UI']">
    <div class="flex-1 p-[30px] overflow-y-auto">
        <h1 class="text-[1.5rem] mb-5 pb-[10px] border-b-2 border-[#1a2a6c] text-[#1a2a6c] font-bold">Заявки</h1>

        @if($loadError)
            <div class="mb-5 bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-[10px]">
                ❌ Не удалось загрузить данные. Попробуйте позже.
            </div>
        @endif

        @if(session()->has('success'))
            <div class="mb-5 bg-green-500 text-white px-5 py-4 rounded-[10px] shadow">
                {{ session('success') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="mb-5 bg-red-500 text-white px-5 py-4 rounded-[10px] shadow">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-5">
            <x-button onclick="window.location.href='{{ route('admin.classrooms') }}'">Аудитории</x-button>
        </div>

        <x-filter :classrooms="$classrooms"/>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] p-5">
            @forelse($bookings as $booking)
                <x-booking-card :booking="$booking">
                    
                    <x-slot name="actions">
                        @if($booking->status === 'pending')
                            <x-input wire:model="adminComments.{{ $booking->id }}" type="textarea"
                                    class="resize-none" placeholder="Оставить комментарий..."/>

                            <div class="flex gap-[10px]">
                                <button wire:click="updateStatus({{ $booking->id }}, 'approved')" 
                                        class="flex-1 py-2 bg-gradient-to-br from-[#1f9d55] to-[#27ae60] transition-all
                                        duration-300 shadow-[0_4px_15px_rgba(39,174,96,0.3)] text-white rounded-lg
                                        font-semibold hover:shadow-[0_6px_20px_rgba(39,174,96,0.45)] cursor-pointer">
                                    Принять
                                </button>
                                <button wire:click="updateStatus({{ $booking->id }}, 'rejected')" 
                                        class="flex-1 py-2 bg-gradient-to-br from-[#c2433a] to-[#EB4C42] transition-all
                                        duration-300 shadow-[0_4px_15px_rgba(231,76,60,0.3)] text-white rounded-lg
                                        font-semibold hover:shadow-[0_6px_20px_rgba(231,76,60,0.45)] cursor-pointer">
                                    Отклонить
                                </button>
                            </div>
                        @else
                            <div class="text-center text-[18px] font-bold">
                                @if($booking->status === 'approved')
                                    <div class="flex justify-center items-center gap-3">
                                        <span class="text-green-600 font-bold text-[18px]">✅ Одобрена</span>
                                        <span class="text-gray-300">|</span>
                                        <button wire:click="openCancelModal({{ $booking->id }})" 
                                                class="text-[#d64545] text-[14px] font-semibold transition-all
                                                duration-200 hover:text-[#bb2d2d] cursor-pointer">
                                            Отменить бронь
                                        </button>
                                    </div>
                                @elseif($booking->status === 'rejected')
                                    <div class="text-red-600">❌ Отклонена</div>
                                @elseif($booking->status === 'cancelled')
                                    <div class="text-gray-500">🚫 Отменена</div>
                                @endif
                            </div>
                        @endif
                    </x-slot>
                </x-booking-card>
            @empty
                <div class="col-span-full text-2xl font-bold text-center text-gray-400 py-10">Заявок нет</div>
            @endforelse

            @if($hasMoreBookings)
                <div class="col-span-full flex justify-center mt-2">
                    <x-button wire:click="loadMore">Показать ещё</x-button>
                </div>
            @endif
        </div>

        @if($showCancelModal)
            <x-modal title="Подтверждение отмены">
                <p class="text-gray-600 mb-6">Вы уверены, что хотите отменить эту бронь? Действие нельзя будет отменить.</p>
                <x-slot name="footer">
                    <x-button wire:click="cancelBooking" color="red">Да, отменить</x-button>
                    <x-button wire:click="closeCancelModal" color="gray">Нет, оставить</x-button>
                </x-slot>
            </x-modal>
        @endif

        <div wire:loading.flex wire:target="updateStatus, cancelBooking"
            class="fixed inset-0 bg-[rgba(255,255,255,0.7)] z-[9999] flex-col justify-center items-center">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-3 text-primary font-semibold">Загрузка...</p>
        </div>
    </div>
</div>