<div class="min-h-screen bg-[#f5f7fa] text-[#333] font-['Segoe_UI']">
    <div class="flex-1 p-[30px] overflow-y-auto">

        <h1 class="text-[1.5rem] mb-5 pb-[10px] border-b-2 border-[#1a2a6c] text-[#1a2a6c] font-bold">
            🔧 Панель технического специалиста
        </h1>

        <x-filter :classrooms="$classrooms" :showStatus="false"/>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] p-5">
            @forelse($bookings as $booking)
                <x-booking-card :booking="$booking" :isTech="true" />
            @empty
                <div class="col-span-full text-center text-2xl font-bold text-gray-400 py-10">
                    Нет заявок, требующих оборудования или помощи в подключении
                </div>
            @endforelse
        </div>
    </div>
</div>