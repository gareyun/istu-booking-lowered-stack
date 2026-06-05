<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Classroom;
use App\Models\Booking;
use Carbon\Carbon;

class ClassroomSchedule extends Component
{
    protected $layout = 'components.layout';
    
    public $selectedClassroom = null;
    public $weekStart;
    public $classrooms = [];

    protected $queryString = ['selectedClassroom', 'weekStart'];

    public $showBookingModal = false;
    public $selectedBooking = null;

    public function mount()
    {
        $this->classrooms = Classroom::orderBy('room')->get();
        $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function updatedSelectedClassroom()
    {
        $this->weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function nextWeek()
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function prevWeek()
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function getWeekDaysProperty()
    {
        $days = [];
        $start = Carbon::parse($this->weekStart);
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'dayName' => $day->translatedFormat('D'),
                'dayNumber' => $day->format('d'),
                'fullDate' => $day->format('d.m.Y'),
            ];
        }
        return $days;
    }

    public function getTimeSlotsProperty()
    {
        $slots = [];
        $start = Carbon::parse('08:00');
        $end = Carbon::parse('22:00');
        while ($start < $end) {
            $slots[] = $start->format('H:i');
            $start->addMinutes(30);
        }
        return $slots;
    }

    public function getBookingsProperty()
    {
        if (!$this->selectedClassroom) {
            return collect();
        }

        $weekDates = collect($this->weekDays)->pluck('fullDate')->toArray();

        return Booking::where('classroom_id', $this->selectedClassroom)
            ->where('status', 'approved')
            ->whereIn('date', $weekDates)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function openBookingModal($bookingId)
    {
        $this->selectedBooking = Booking::with(['classroom', 'user'])
            ->findOrFail($bookingId);

        $this->showBookingModal = true;
    }

    public function closeBookingModal()
    {
        $this->showBookingModal = false;
        $this->selectedBooking = null;
    }

    public function render()
    {
        return view('livewire.classroom-schedule', [
            'bookings' => $this->bookings,
            'weekDays' => $this->weekDays,
            'timeSlots' => $this->timeSlots,
        ]);
    }
}