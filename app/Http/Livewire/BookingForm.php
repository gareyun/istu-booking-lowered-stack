<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Classroom;
use App\Models\User;
use Carbon\Carbon;

class BookingForm extends Component
{
    protected $layout = 'components.layout';

    public $classrooms = [];
    public $bookings = [];

    public $classroom_id;
    public $date;
    public $start_time;
    public $end_time;
    public $purpose;
    public $equipment;
    public $is_tech_support = 0;
    public $user_comment;

    public $busySlots = [];

    public $filterStatus = '';
    public $filterDate = '';
    public $filterClassroom = '';

    public $showSettingsModal = false;
    public $settingsVkLink = '';
    public $settingsPhone = '';

    public $submitted = false;

    public $perPage = 5;
    public $hasMoreBookings = false;

    public $loadError = false;

    public function mount()
    {
        $this->classrooms = Classroom::all();

        $this->loadBookings();

        $user = User::find(1);
        if ($user) {
            $this->settingsVkLink = $user->vk_link ?? '';
            $this->settingsPhone = $user->phone ?? '';
        }
    }

    public function loadBookings()
    {
        try {
            $query = User::find(1)
                ->bookings()
                ->with('classroom');

            if ($this->filterStatus) {
                $query->where('status', $this->filterStatus);
            }

            if ($this->filterDate) {
                $query->where('date', $this->filterDate);
            }

            if ($this->filterClassroom) {
                $query->where('classroom_id', $this->filterClassroom);
            }

            $this->bookings = $query
                ->latest()
                ->take($this->perPage)
                ->get();

            $this->hasMoreBookings = $query->count() > $this->perPage;

            $this->loadError = false;
        } catch (\Exception $e) {

            \Illuminate\Support\Facades\Log::error(
                'Booking load error: ' . $e->getMessage()
            );

            $this->bookings = [];
            $this->hasMoreBookings = false;
            $this->loadError = true;
        }
        
    }

    public function loadMore()
    {
        $this->perPage += 5;
        $this->loadBookings();
    }

    public function updatedClassroomId()
    {
        $this->loadBusySlots();
    }

    public function updatedDate()
    {
        $this->loadBusySlots();
    }

    public function updatedFilterStatus()
    {
        $this->loadBookings();
    }

    public function updatedFilterDate()
    {
        $this->loadBookings();
    }

    public function updatedFilterClassroom()
    {
        $this->loadBookings();
    }

    public function resetFilters()
    {
        $this->reset([
            'filterStatus',
            'filterDate',
            'filterClassroom'
        ]);

        $this->loadBookings();

        $this->emit('resetFilterDate');
    }

    public function loadBusySlots()
    {
        if (!$this->classroom_id || !$this->date) {
            $this->busySlots = [];
            return;
        }

        $this->busySlots = Booking::where('classroom_id', $this->classroom_id)
            ->where('date', $this->date)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_time', 'end_time', 'status'])
            ->toArray();
    }

    public function openSettingsModal()
    {
        $this->showSettingsModal = true;
    }

    public function closeSettingsModal()
    {
        $this->showSettingsModal = false;
    }

    public function saveSettings()
    {
        $this->validate([
            'settingsVkLink' => 'nullable|string|max:255',
            'settingsPhone'  => 'required|string|max:20'
        ]);

        $user = User::find(1);
        if ($user) {
            $user->update([
                'vk_link' => $this->settingsVkLink,
                'phone'   => $this->settingsPhone
                ]);
            session()->flash('success', 'Настройки сохранены.');
        }

        $this->closeSettingsModal();
    }

    public function submit()
    {
        $user = User::find(1);
        
        if (!$user || !$user->phone) {
            $this->addError('phone', 'Для отправки заявки необходимо указать номер телефона в настройках.');
            $this->openSettingsModal();
            return;
        }

        $validated = $this->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'purpose' => 'required|string|max:255',
            'equipment' => 'nullable|string',
            'is_tech_support' => 'required|boolean',
            'user_comment' => 'nullable|string'
        ]);

        $vkLink = $user ? $user->vk_link : null;

        try {
            $bookingStart = Carbon::createFromFormat(
                'd.m.Y H:i',
                trim($validated['date']) . ' ' . trim($validated['start_time'])
            );
        } catch (\Exception $e) {
            $this->addError('date', 'Некорректный формат даты или времени.');
            return;
        }

        if ($bookingStart->lessThanOrEqualTo(now()->addHours(24))) {
            $this->addError('date', 'Забронировать аудиторию можно не позднее чем за 24 часа до начала мероприятия.');
            return;
        }

        $exists = Booking::where('classroom_id', $validated['classroom_id'])
            ->where('date', $validated['date'])
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                    ->where('end_time', '>', $validated['start_time']);
            })
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            $this->addError('busy', 'Выбранное время уже занято');
            return;
        }

        $booking = Booking::create([
            'user_id' => 1,
            ...$validated,
            'vk_link' => $vkLink
        ]);

        $booking->load('classroom');

        try {
            app(\App\Services\VkNotificationService::class)->notifyBookingCreated($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'VK notification create booking error: ' . $e->getMessage()
            );
        }

        session()->flash('success', 'Заявка успешно создана');
        $this->submitted = true; 

        $this->reset([
            'classroom_id',
            'date',
            'start_time',
            'end_time',
            'purpose',
            'equipment',
            'user_comment'
        ]);
        $this->is_tech_support = 0;

        $this->loadBookings();
        $this->loadBusySlots();
    }

    public function resetForm()
    {
        $this->submitted = false;
        $this->loadBusySlots();
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}