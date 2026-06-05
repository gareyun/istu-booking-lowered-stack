<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\ClassroomCategory;
use App\Services\GoogleCalendarService;

class Classrooms extends Component
{
    protected $layout = 'components.layout';
    
    public $classrooms;
    public $categories;
    public $buildings;
    public $buildingTypes;

    // формы
    public $newCategory;
    public $newBuildingName;
    public $newBuildingAddress;
    public $newBuildingDescription;
    public $newBuildingTypeId;
    public $newType;

    public $showCategoryModal = false;
    public $showBuildingModal = false;
    public $showTypeModal = false;

    public $showBuildingListModal = false;
    public $showBuildingEditModal = false;

    public $editingBuildingId = null;

    public $editBuildingName;
    public $editBuildingAddress;
    public $editBuildingDescription;
    public $editBuildingTypeId;

    public $showCreateModal = false;
    public $showEditModal = false;

    public $editingId = null;

    public $room, $description, $equipment, $capacity, $google_calendar_id;
    public $classroom_category_id, $building_id;

    public $successMessage = null;
    public $errorMessage = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->classrooms = Classroom::with(['category', 'building'])->get();
        $this->categories = ClassroomCategory::all();
        $this->buildings = Building::with('type')->get();
        $this->buildingTypes = BuildingType::all();
    }

    public function openCreateModal()
    {
        $this->resetFields();

        $this->classroom_category_id = $this->categories->first()->id ?? null;
        $this->building_id = $this->buildings->first()->id ?? null;

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $classroom = Classroom::findOrFail($id);

        $this->editingId = $id;
        $this->room = $classroom->room;
        $this->description = $classroom->description;
        $this->equipment = $classroom->equipment;
        $this->capacity = $classroom->capacity;
        $this->google_calendar_id = $classroom->google_calendar_id;
        $this->classroom_category_id = $classroom->classroom_category_id;
        $this->building_id = $classroom->building_id;

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->showEditModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'room' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'equipment' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'google_calendar_id' => 'required|string|max:100',
            'classroom_category_id' => 'required|exists:classroom_categories,id',
            'building_id' => 'required|exists:buildings,id',
        ]);

        $validated['google_calendar_id'] = GoogleCalendarService::resolveCalendarId(
            $validated['google_calendar_id']
        );

        Classroom::create($validated);

        $this->closeModal();
        $this->loadData();
        $this->successMessage = 'Аудитория добавлена';
    }

    public function update()
    {
        $validated = $this->validate([
            'room' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'equipment' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'google_calendar_id' => 'required|string|max:100',
            'classroom_category_id' => 'required|exists:classroom_categories,id',
            'building_id' => 'required|exists:buildings,id',
        ]);

        $validated['google_calendar_id'] = GoogleCalendarService::resolveCalendarId(
            $validated['google_calendar_id']
        );

        Classroom::findOrFail($this->editingId)->update($validated);

        $this->closeModal();
        $this->loadData();
        $this->successMessage = 'Аудитория обновлена';
    }

    public function delete($id)
    {
        $classroom = Classroom::find($id);

        if (!$classroom) {
            $this->errorMessage = 'Аудитория не найдена.';
            return;
        }

        if ($classroom->bookings()->whereIn('status', ['approved', 'pending'])->exists()) {
            $this->errorMessage = 'Нельзя удалить аудиторию – есть связанные заявки.';
            return;
        }

        $classroom->delete();
        $this->loadData();
        $this->successMessage = 'Аудитория успешно удалена.';
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function closeBuildingModal()
    {
        $this->showBuildingModal = false;
    }

    private function resetFields()
    {
        $this->reset([
            'room', 'description', 'equipment', 'capacity',
            'google_calendar_id', 'classroom_category_id', 'building_id'
        ]);
    }

    public function toggleCategoryModal() {
        $this->showCategoryModal = !$this->showCategoryModal;
    }

    public function createCategory()
    {
        $this->validate([
            'newCategory' => 'required|string|max:255',
        ]);

        ClassroomCategory::create([
            'category' => $this->newCategory
        ]);

        $this->showCategoryModal = false;
        $this->newCategory = null;
        $this->loadData();
    }

    public function openBuildingModal()
    {
        $this->newBuildingTypeId = $this->buildingTypes->first()->id ?? null;

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->showBuildingModal = true;
    }

    public function toggleTypeModal() {
        $this->showTypeModal = !$this->showTypeModal;
    }

    public function createBuildingType()
    {
        $this->validate([
            'newType' => 'required|string|max:255',
        ]);

        BuildingType::create([
            'type' => $this->newType
        ]);

        $this->showTypeModal = false;
        $this->newType = null;
        $this->loadData();
    }

    public function createBuilding()
    {
        $this->validate([
            'newBuildingName' => 'required|string|max:255',
            'newBuildingAddress' => 'required|string|max:255',
            'newBuildingDescription' => 'nullable|string',
            'newBuildingTypeId' => 'required|exists:building_types,id',
        ]);

        Building::create([
            'name' => $this->newBuildingName,
            'address' => $this->newBuildingAddress,
            'description' => $this->newBuildingDescription,
            'building_type_id' => $this->newBuildingTypeId,
        ]);

        $this->successMessage = 'Корпус успешно создан';

        $this->closeBuildingModal();

        $this->reset([
            'newBuildingName',
            'newBuildingAddress',
            'newBuildingDescription',
            'newBuildingTypeId'
        ]);

        $this->loadData();
    }

    public function openBuildingListModal()
    {
        $this->loadData();

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->showBuildingListModal = true;
    }

    public function closeBuildingListModal()
    {
        $this->showBuildingListModal = false;
    }

    public function openBuildingEditModal($id)
    {
        $building = Building::findOrFail($id);

        $this->editingBuildingId = $id;

        $this->editBuildingName = $building->name;
        $this->editBuildingAddress = $building->address;
        $this->editBuildingDescription = $building->description;
        $this->editBuildingTypeId = $building->building_type_id;

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->showBuildingEditModal = true;
    }

    public function closeBuildingEditModal()
    {
        $this->showBuildingEditModal = false;

        $this->reset([
            'editingBuildingId',
            'editBuildingName',
            'editBuildingAddress',
            'editBuildingDescription',
            'editBuildingTypeId'
        ]);
    }

    public function updateBuilding()
    {
        $this->validate([
            'editBuildingName' => 'required',
            'editBuildingAddress' => 'required',
            'editBuildingTypeId' => 'required',
        ]);

        Building::findOrFail(
            $this->editingBuildingId
        )->update([
            'name' => $this->editBuildingName,
            'address' => $this->editBuildingAddress,
            'description' => $this->editBuildingDescription,
            'building_type_id' => $this->editBuildingTypeId,
        ]);

        $this->closeBuildingEditModal();

        $this->loadData();
    }

    public function deleteBuilding($id)
    {
        $building = Building::findOrFail($id);

        if ($building->classrooms()->exists()) {
            $this->errorMessage = 'Нельзя удалить аудиторию, есть связанные заявки';
            return;
        }

        $building->delete();

        $this->loadData();
    }

    public function deleteCategory($id)
    {
        $category = ClassroomCategory::findOrFail($id);

        if ($category->classrooms()->exists()) {
            $this->errorMessage = 'Нельзя удалить категорию, так как есть связанные аудитории';
            return;
        }

        $category->delete();

        if ($this->classroom_category_id == $id) {
            $this->classroom_category_id = $this->categories
                ->where('id', '!=', $id)
                ->first()?->id;
        }

        $this->successMessage = 'Категория удалена';

        $this->loadData();
    }

    public function deleteBuildingType($id)
    {
        $type = BuildingType::findOrFail($id);

        if ($type->buildings()->exists()) {
            $this->errorMessage = 'Нельзя удалить тип корпуса, так как есть связанные корпуса';
            return;
        }

        $type->delete();

        if ($this->newBuildingTypeId == $id) {
            $this->newBuildingTypeId = $this->buildingTypes
                ->where('id', '!=', $id)
                ->first()?->id;
        }

        $this->successMessage = 'Тип корпуса удалён';

        $this->loadData();
    }

    public function render()
    {
        return view('livewire.admin.classrooms');
    }
}