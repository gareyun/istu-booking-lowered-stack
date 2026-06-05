<div class="flex-1 p-[30px] overflow-y-auto">
    <h1 class="text-2xl mb-5 pb-2.5 border-b-2 border-[#1a2a6c] text-[#1a2a6c]">Аудитории</h1>

    @if ($successMessage)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ $successMessage }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="mb-5 flex gap-3">
        <x-button onclick="window.location.href='{{ route('admin') }}'">Заявки</x-button>
        <x-button wire:click="openCreateModal">+ Добавить аудиторию</x-button>
        <x-button wire:click="openBuildingModal">+ Добавить корпус</x-button>
        <x-button wire:click="openBuildingListModal">Корпуса</x-button>
    </div>

    <div class="applications-list">
        @foreach ($classrooms as $classroom)
            <div class="bg-white rounded-[10px] shadow-[0_4px_15px_rgba(0,0,0,0.1)]
                            p-5 mb-5 transition-transform duration-300 ease-in-out">
                <div class="text-xl font-bold text-[#1a2a6c] mb-[5px]">{{ $classroom->room }}</div>

                <div class="my-2 flex justify-between">
                    <div class="font-bold text-[#7f8c8d]">Тип аудитории:</div>
                    <div>{{ $classroom->category->category }}</div>
                </div>

                <div class="my-2 flex justify-between">
                    <div class="font-bold text-[#7f8c8d]">Корпус:</div>
                    <div>{{ $classroom->building->name }}</div>
                </div>

                <div class="my-2 flex justify-between">
                    <div class="font-bold text-[#7f8c8d]">Описание:</div>
                    <div>{{ $classroom->description }}</div>
                </div>

                <div class="my-2 flex justify-between">
                    <div class="font-bold text-[#7f8c8d]">Оборудование:</div>
                    <div>{{ $classroom->equipment }}</div>
                </div>

                <div class="my-2 flex justify-between">
                    <div class="font-bold text-[#7f8c8d]">Вместимость:</div>
                    <div>{{ $classroom->capacity }}</div>
                </div>

                <div class="w-full flex mt-5 gap-3">
                    <x-button wire:click="openEditModal({{ $classroom->id }})">✏️ Редактировать</x-button>
                    <x-button wire:click="delete({{ $classroom->id }})" color="red">🗑 Удалить</x-button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CREATE ROOM MODAL --}}
    @if($showCreateModal)
        <x-modal title="Добавить аудиторию">
            <p class="mb-[5px] text-lg font-semibold">Аудитория</p>
            <x-input wire:model="room" placeholder="Номер аудитории" class="mb-4"/>

            <div class="flex justify-between items-center mb-4">
                <x-select wire:model="classroom_category_id">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category }}</option>
                    @endforeach
                </x-select>

                <x-button wire:click="toggleCategoryModal" class="ml-2">
                    {!! $showCategoryModal ? '&times;' : '+' !!}
                </x-button>
            </div>

            @if($showCategoryModal)
                <div class="mb-5">
                    <div class="space-y-2 mb-4 max-h-[180px] overflow-y-auto">
                        @forelse($categories as $category)
                            <div class="flex items-center justify-between border border-gray-200 rounded-[10px] px-3 py-2">
                                <span class="text-base">{{ $category->category }}</span>
                                <button wire:click="deleteCategory({{ $category->id }})"
                                        type="button"
                                        class="text-[#c2433a] cursor-pointer mt-[-5px]">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Категорий пока нет</div>
                        @endforelse
                    </div>

                    <x-input wire:model="newCategory" placeholder="Категория" class="mb-4"/>
                    <x-button wire:click="createCategory" class="w-full">Сохранить</x-button>
                </div>
            @endif

            <div class="flex justify-between items-center mb-4">
                <x-select wire:model="building_id">
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}">{{ $building->name }}</option>
                    @endforeach
                </x-select>
                <x-button wire:click="openBuildingModal" class="ml-2">+</x-button>
            </div>

            <p class="mb-[5px] text-lg font-semibold">Описание</p>
            <x-input wire:model="description" type="textarea" placeholder="Описание" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Оборудование</p>
            <x-input wire:model="equipment" type="textarea" placeholder="Оборудование, имеющееся в аудитории" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Вместимость</p>
            <x-input wire:model="capacity" type="number" placeholder="Количество человек" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Идентификатор Google Calendar</p>
            <x-input wire:model="google_calendar_id" placeholder="ID календаря или ссылка Google Calendar" class="mb-4"/>

            <x-slot name="footer">
                <x-button wire:click="save">Сохранить</x-button>
                <x-button wire:click="closeModal" color="red">Отмена</x-button>
            </x-slot>
        </x-modal>
    @endif

    {{-- CREATE BUILDING MODAL --}}
    @if($showBuildingModal)
        <x-modal title="Добавить корпус">
            <p class="mb-[5px] text-lg font-semibold">Корпус</p>
            <x-input wire:model="newBuildingName" placeholder="Название" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Тип корпуса</p>
            <div class="flex justify-between items-center mb-4">
                <x-select wire:model="newBuildingTypeId">
                    @foreach($buildingTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->type }}</option>
                    @endforeach
                </x-select>
                <x-button wire:click="toggleTypeModal" class="ml-2">{!! $showTypeModal ? '&times;' : '+' !!}</x-button>
            </div>

            @if($showTypeModal)
                <div class="mb-5">
                    <div class="space-y-2 mb-4 max-h-[180px] overflow-y-auto">
                        @forelse($buildingTypes as $type)
                            <div class="flex items-center justify-between border border-gray-200 rounded-[10px] px-3 py-2">
                                <span class="text-base">{{ $type->type }}</span>

                                <button wire:click="deleteBuildingType({{ $type->id }})"
                                    type="button" class="text-[#c2433a] cursor-pointer mt-[-5px]">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Типов пока нет</div>
                        @endforelse
                    </div>

                    <x-input wire:model="newType" placeholder="Тип корпуса" class="mb-4"/>
                    <x-button wire:click="createBuildingType" class="w-full">Сохранить</x-button>
                </div>
            @endif

            <p class="mb-[5px] text-lg font-semibold">Адрес</p>
            <x-input wire:model="newBuildingAddress" placeholder="ул. Студенческая" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Описание</p>
            <x-input wire:model="newBuildingDescription" type="textarea" placeholder="Корпус IT технологий" class="mb-4"/>

            <x-slot name="footer">
                <x-button wire:click="createBuilding">Сохранить</x-button>
                <x-button color="red" wire:click="closeBuildingModal">Отмена</x-button>
            </x-slot>
        </x-modal>
    @endif

    {{-- BUILDINGS LIST MODAL --}}
    @if($showBuildingListModal)
        <x-modal title="Управление корпусами" width="900px">
            <div class="p-2">
                @forelse($buildings as $building)
                    <div class="bg-white rounded-[10px] shadow-[0_4px_15px_rgba(0,0,0,0.1)] p-5 mb-5">

                        <div class="text-xl font-bold text-[#1a2a6c] mb-[5px]">
                            {{ $building->name }}
                        </div>

                        <div class="my-2 flex justify-between">
                            <div class="font-bold text-[#7f8c8d]">Тип:</div>
                            <div>{{ $building->type->type }}</div>
                        </div>

                        <div class="my-2 flex justify-between">
                            <div class="font-bold text-[#7f8c8d]">Адрес:</div>
                            <div>{{ $building->address }}</div>
                        </div>

                        <div class="my-2 flex justify-between">
                            <div class="font-bold text-[#7f8c8d]">Описание:</div>
                            <div>{{ $building->description }}</div>
                        </div>

                        <div class="w-full flex mt-5 gap-3">
                            <x-button wire:click="openBuildingEditModal({{ $building->id }})">✏️ Редактировать</x-button>
                            <x-button wire:click="deleteBuilding({{ $building->id }})" color="red">🗑 Удалить</x-button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-[#7f8c8d] py-10">Корпусов пока нет</div>
                @endforelse
            </div>
            
            <x-slot name="footer">
                <x-button wire:click="closeBuildingListModal" color="red">Закрыть</x-button>
            </x-slot>
        </x-modal>
    @endif

    {{-- BUILDING EDIT MODAL --}}
    @if($showBuildingEditModal)
        <x-modal title="Редактировать корпус">
            <p class="mb-[5px] text-lg font-semibold">Название</p>
            <x-input wire:model="editBuildingName" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Тип корпуса</p>
            <x-select wire:model="editBuildingTypeId">
                @foreach($buildingTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                @endforeach
            </x-select>

            <p class="mb-[5px] text-lg font-semibold">Адрес</p>
            <x-input wire:model="editBuildingAddress" class="mb-4"/>

            <p class="mb-[5px] text-lg font-semibold">Описание</p>
            <x-input wire:model="editBuildingDescription" type="textarea" class="mb-4"/>

            <x-slot name="footer">
                <x-button wire:click="updateBuilding">Сохранить</x-button>
                <x-button wire:click="closeBuildingEditModal" color="red">Отмена</x-button>
            </x-slot>
        </x-modal>
    @endif

    {{-- ROOM EDIT MODAL --}}
    @if($showEditModal)
        <x-modal title="Редактировать аудиторию">
            <p class="mb-[5px] text-lg font-semibold">Аудитория</p>
            <x-input wire:model="room" class="mb-3" />

            <x-select wire:model="classroom_category_id" wrapper-class="mb-3">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category }}</option>
                @endforeach
            </x-select>

            <x-select wire:model="building_id" wrapper-class="mb-3">
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                @endforeach
            </x-select>

            <p class="mb-[5px] text-lg font-semibold">Описание</p>
            <x-input wire:model="description" type="textarea" class="mb-2" />

            <p class="mb-[5px] text-lg font-semibold">Оборудование</p>
            <x-input wire:model="equipment" type="textarea" class="mb-2" />

            <p class="mb-[5px] text-lg font-semibold">Вместимость</p>
            <x-input wire:model="capacity" type="number" class="mb-3" />

            <p class="mb-[5px] text-lg font-semibold">Идентификатор Google Calendar</p>
            <x-input wire:model="google_calendar_id" class="mb-3" />

            <x-slot name="footer">
                <x-button wire:click="update">Сохранить</x-button>
                <x-button wire:click="closeModal" color="red">Отмена</x-button>
            </x-slot>
        </x-modal>
    @endif
</div>