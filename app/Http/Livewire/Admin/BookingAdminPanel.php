<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Classroom;
use App\Services\GoogleCalendarService;
use App\Services\VkNotificationService;
use Carbon\Carbon;

class BookingAdminPanel extends Component
{
    protected $layout = 'components.layout';

    public $filterStatus = '';
    public $filterDate = '';
    public $filterClassroom = '';
    public $adminComments = [];

    public $showCancelModal = false;
    public $bookingToCancel = null;

    public $perPage = 10;
    public $hasMoreBookings = false;

    public $loadError = false;

    public function setStatus($filterStatus = '')
    {
        $this->filterStatus = $filterStatus;
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function updateStatus($bookingId, $action)
    {
        $booking = Booking::with('classroom')->findOrFail($bookingId);

        if ($action === 'approved') {
            try {
                $eventId = app(GoogleCalendarService::class)->createEvent($booking);
                $booking->status = 'approved';
                $booking->google_event_id = $eventId;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Google Calendar error: ' . $e->getMessage());

                session()->flash(
                    'error',
                    'Не удалось создать событие в Google Календаре: ' . $e->getMessage()
                );

                return;
            }
        } elseif ($action === 'rejected') {
            $booking->status = 'rejected';
        }

        $booking->admin_comment = $this->adminComments[$bookingId] ?? null;
        $booking->save();

        try {
            app(VkNotificationService::class)->notifyStatusChange($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('VK notification error: ' . $e->getMessage());
        }

        session()->flash('success', 'Статус заявки обновлён');
    }

    public function openCancelModal($bookingId)
    {
        $this->bookingToCancel = $bookingId;
        $this->showCancelModal = true;
    }

    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->bookingToCancel = null;
    }

    public function cancelBooking()
    {
        if (!$this->bookingToCancel) {
            return;
        }

        $booking = Booking::with('classroom')->findOrFail($this->bookingToCancel);

        if ($booking->status !== 'approved') {
            session()->flash('error', 'Можно отменить только одобренную бронь.');
            $this->closeCancelModal();
            return;
        }

        try {
            $startDateTime = Carbon::createFromFormat(
                'd.m.Y H:i',
                $booking->date . ' ' . $booking->start_time
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Некорректный формат даты/времени.');
            $this->closeCancelModal();
            return;
        }

        if ($startDateTime->lessThan(now()->addHours(24))) {
            session()->flash('error', 'Отмена возможна не позднее чем за 24 часа до начала.');
            $this->closeCancelModal();
            return;
        }

        try {
            app(GoogleCalendarService::class)->deleteEvent($booking);
            $booking->google_event_id = null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Google Calendar delete error: ' . $e->getMessage()
            );

            session()->flash('error', 'Не удалось удалить событие из Google Календаря.');

            $this->closeCancelModal();
            return;
        }

        $booking->status = 'cancelled';
        $booking->save();

        try {
            app(VkNotificationService::class)->notifyStatusChange($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('VK notification error: ' . $e->getMessage());
        }

        session()->flash('success', 'Бронь отменена.');
        $this->closeCancelModal();
    }

    public function getBookingsProperty()
    {
        try {
            $query = Booking::with(['classroom', 'user'])
                ->when($this->filterStatus, function ($query) {
                    $query->where('status', $this->filterStatus);
                })
                ->when($this->filterDate, function ($query) {
                    $query->where('date', $this->filterDate);
                })
                ->when($this->filterClassroom, function ($query) {
                    $query->where('classroom_id', $this->filterClassroom);
                })
                ->orderByDesc('id');

            $this->hasMoreBookings = $query->count() > $this->perPage;

            $this->loadError = false;

            return $query
                ->take($this->perPage)
                ->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Admin booking load error: ' . $e->getMessage()
            );

            $this->loadError = true;
            $this->hasMoreBookings = false;

            return collect();
        }
    }

    public function resetFilters()
    {
        $this->filterStatus = '';
        $this->filterDate = '';
        $this->filterClassroom = '';
        $this->perPage = 10;
        $this->emit('resetFilterDate');
    }

    public function render()
    {
        return view('livewire.admin.booking-admin-panel', [
            'bookings' => $this->bookings,
            'classrooms' => Classroom::orderBy('room')->get(),
        ]);
    }
}