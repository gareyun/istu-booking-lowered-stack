<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Classroom;

class TechSupportPanel extends Component
{
    protected $layout = 'components.layout';

    public $filterDate = '';
    public $filterClassroom = '';

    public function getBookingsProperty()
    {
        return Booking::with(['classroom', 'user'])
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->where('is_tech_support', true)
                    ->orWhere(function ($q) {
                        $q->whereNotNull('equipment')
                            ->where('equipment', '!=', '');
                    });
            })
            ->when($this->filterDate, function ($query) {
                $query->where('date', $this->filterDate);
            })
            ->when($this->filterClassroom, function ($query) {
                $query->where('classroom_id', $this->filterClassroom);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function resetFilters()
    {
        $this->reset(['filterDate', 'filterClassroom']);
        $this->emit('resetFilterDate');
    }

    public function render()
    {
        return view('livewire.tech-support-panel', [
            'bookings' => $this->bookings,
            'classrooms' => Classroom::orderBy('room')->get(),
        ]);
    }
}