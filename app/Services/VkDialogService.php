<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Classroom;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class VkDialogService
{
    protected VkBotService $vk;

    public function __construct(VkBotService $vk)
    {
        $this->vk = $vk;
    }

    public function handleMessage(int $userId, string $text): void
    {
        $text = trim($text);

        // сброс
        if (in_array(mb_strtolower($text), ['/start', 'начать', 'старт', 'начать заново'])) {
            Cache::forget("vk_bot_state_{$userId}");
            $this->sendStepClassroom($userId, false);
            return;
        }

        $state = Cache::get("vk_bot_state_{$userId}");

        if (!$state) {
            $this->sendStepClassroom($userId, false);
            return;
        }

        switch ($state['step']) {
            case 'classroom':
                $this->processClassroom($userId, $text, $state);
                break;
            case 'date':
                $this->processDate($userId, $text, $state);
                break;
            case 'start_time':
                $this->processStartTime($userId, $text, $state);
                break;
            case 'end_time':
                $this->processEndTime($userId, $text, $state);
                break;
            case 'purpose':
                $this->processPurpose($userId, $text, $state);
                break;
            case 'name':
                $this->processName($userId, $text, $state);
                break;
            case 'faculty':
                $this->processFaculty($userId, $text, $state);
                break;
            case 'group':
                $this->processGroup($userId, $text, $state);
                break;
            case 'phone':
                $this->processPhone($userId, $text, $state);
                break;
            case 'equipment':
                $this->processEquipment($userId, $text, $state);
                break;
            case 'tech_support':
                $this->processTechSupport($userId, $text, $state);
                break;
            case 'comment':
                $this->processComment($userId, $text, $state);
                break;
            case 'confirm':
                $this->processConfirm($userId, $text, $state);
                break;
            default:
                // Сброс при неизвестном шаге
                Cache::forget("vk_bot_state_{$userId}");
                $this->sendStepClassroom($userId, true);
        }
    }

    protected function sendStepClassroom(int $userId, bool $error = false): void
    {
        $classrooms = Classroom::orderBy('room')->get();
        $buttons = [];
        $row = [];
        foreach ($classrooms as $i => $room) {
            $row[] = ['text' => $room->room];
            if (count($row) == 2 || $i == count($classrooms) - 1) {
                $buttons[] = $row;
                $row = [];
            }
        }

        $buttons[] = [['text' => 'Отмена', 'color' => 'negative']];

        $message = ($error ? "❌ Не удалось распознать аудиторию.\n" : "📅 ")
            . "Выберите аудиторию из списка или введите её номер/название:";

        $this->vk->sendMessageWithKeyboard($userId, $message, $buttons, true);
        Cache::put("vk_bot_state_{$userId}", ['step' => 'classroom'], now()->addMinutes(30));
    }

    protected function processClassroom(int $userId, string $text, array $state): void
    {
        if (mb_strtolower($text) === 'отмена') {
            Cache::forget("vk_bot_state_{$userId}");
            $this->vk->sendMessage($userId, "🚫 Бронирование отменено.");
            return;
        }

        $classroom = Classroom::where('room', $text)
            ->orWhere('room', 'like', "%{$text}%")
            ->first();

        if (!$classroom) {
            $this->sendStepClassroom($userId, true);
            return;
        }

        $state['classroom_id'] = $classroom->id;
        $state['classroom_name'] = $classroom->room;
        $state['step'] = 'date';

        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $this->vk->sendMessage($userId, "✅ Аудитория: {$classroom->room}\n"
            . "📅 Введите дату бронирования в формате ДД.ММ.ГГГГ (например, 15.05.2026):");
    }

    protected function processDate(int $userId, string $text, array $state): void
    {
        if (mb_strtolower($text) === 'отмена') {
            Cache::forget("vk_bot_state_{$userId}");
            $this->vk->sendMessage($userId, "🚫 Бронирование отменено.");
            return;
        }

        try {
            $date = Carbon::createFromFormat('d.m.Y', $text);
            if ($date->lt(Carbon::today())) {
                $this->vk->sendMessage($userId, "❌ Дата не может быть в прошлом. Введите ещё раз (ДД.ММ.ГГГГ):");
                return;
            }
        } catch (\Exception $e) {
            $this->vk->sendMessage($userId, "❌ Неверный формат. Введите дату в формате ДД.ММ.ГГГГ (например, 15.05.2026):");
            return;
        }

        $state['date'] = $text;
        $state['step'] = 'start_time';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $this->sendBusySlots(
            $userId,
            $state['classroom_id'],
            $text
        );

        $this->vk->sendMessage($userId, "⏰ Введите время начала (ЧЧ:ММ, например 14:00):");
    }

    protected function sendBusySlots(int $userId, int $classroomId, string $date): void
    {
        $busySlots = Booking::query()
            ->where('classroom_id', $classroomId)
            ->where('date', $date)
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        if ($busySlots->isEmpty()) {
            $this->vk->sendMessage(
                $userId,
                "🟢 На выбранную дату занятых слотов нет.\nМожно выбрать любое свободное время."
            );

            return;
        }

        $message = "📌 Занятые слоты:\n\n";

        foreach ($busySlots as $slot) {
            $message .= "• {$slot->start_time} – {$slot->end_time}\n";
        }

        $this->vk->sendMessage($userId, $message);
    }

    protected function processStartTime(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (!preg_match('/^\d{2}:\d{2}$/', $text)) {
            $this->vk->sendMessage($userId, "❌ Формат должен быть ЧЧ:ММ (например, 14:00). Повторите:");
            return;
        }

        try {
            $bookingDateTime = Carbon::createFromFormat(
                'd.m.Y H:i',
                "{$state['date']} {$text}"
            );

            if ($bookingDateTime->lt(now()->addHours(24))) {
                $this->vk->sendMessage(
                    $userId,
                    "❌ Аудиторию можно бронировать не позднее чем за 24 часа до начала.\n"
                    . "Выберите другую дату или время."
                );

                $state['step'] = 'date';

                Cache::put(
                    "vk_bot_state_{$userId}",
                    $state,
                    now()->addMinutes(30)
                );

                $this->vk->sendMessage(
                    $userId,
                    "📅 Введите новую дату (ДД.ММ.ГГГГ):"
                );

                return;
            }
        } catch (\Exception $e) {
            $this->vk->sendMessage(
                $userId,
                "❌ Не удалось обработать дату и время. Попробуйте снова."
            );

            return;
        }

        $state['start_time'] = $text;
        $state['step'] = 'end_time';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $this->vk->sendMessage($userId, "⏰ Введите время окончания (ЧЧ:ММ):");
    }

    protected function processEndTime(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (!preg_match('/^\d{2}:\d{2}$/', $text)) {
            $this->vk->sendMessage($userId, "❌ Формат времени – ЧЧ:ММ. Повторите:");
            return;
        }

        if ($text <= $state['start_time']) {
            $this->vk->sendMessage($userId, "❌ Время окончания должно быть позже начала. Введите ещё раз:");
            return;
        }

        $busy = Booking::where('classroom_id', $state['classroom_id'])
            ->where('date', $state['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($state, $text) {
                $query->where('start_time', '<', $text)
                      ->where('end_time', '>', $state['start_time']);
            })
            ->exists();

        if ($busy) {
            $this->vk->sendMessage($userId, "❌ Это время уже занято. Введите другую дату или время.\n"
                . "📅 Введите новую дату (ДД.ММ.ГГГГ):");
            $state['step'] = 'date';
            Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));
            return;
        }

        $state['end_time'] = $text;
        $state['step'] = 'purpose';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $this->vk->sendMessage($userId, "🎯 Введите цель бронирования (например, собрание, репетиция):");
    }

    protected function processPurpose(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (mb_strlen($text) < 3) {
            $this->vk->sendMessage($userId, "❌ Слишком короткая цель. Опишите подробнее:");
            return;
        }

        $state['purpose'] = $text;
        $state['step'] = 'name';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $buttons = [
            [['text' => 'Нет', 'color' => 'negative']],
            [['text' => 'Проектор'], ['text' => 'Звук']],
            [['text' => 'Компьютер'], ['text' => 'Другое']],
        ];
        $this->vk->sendMessage($userId, "👤 Введите ваше полное имя (ФИО):");
    }

    protected function processName(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (mb_strlen($text) < 2) {
            $this->vk->sendMessage($userId, "❌ Введите ваше полное имя (ФИО):");
            return;
        }
        $state['name'] = $text;
        $state['step'] = 'faculty';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));
        $this->vk->sendMessage($userId, "🏛 Введите ваш факультет/институт):");
    }

    protected function processFaculty(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (mb_strlen($text) < 2) {
            $this->vk->sendMessage($userId, "❌ Факультет не может быть пустым. Введите ещё раз:");
            return;
        }
        $state['faculty'] = $text;
        $state['step'] = 'group';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));
        $this->vk->sendMessage($userId, "👥 Введите вашу группу (например, Б22-191-2):");
    }

    protected function processGroup(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (mb_strlen($text) < 2) {
            $this->vk->sendMessage($userId, "❌ Группа не может быть пустой. Введите ещё раз:");
            return;
        }
        $state['group'] = $text;
        $state['step'] = 'phone';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $this->vk->sendMessage($userId, "📱 Введите ваш номер телефона:");
    }

    protected function processPhone(int $userId, string $text, array $state): void
    {
        $text = trim($text);

        $digits = preg_replace('/\D/', '', $text);
        if (strlen($digits) < 10) {
            $this->vk->sendMessage($userId, "❌ Неверный формат номера. Введите корректный номер телефона (минимум 10 цифр):");
            return;
        }

        $state['phone'] = $text;
        $state['step'] = 'equipment';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $buttons = [
            [['text' => 'Нет', 'color' => 'negative']],
            [['text' => 'Проектор'], ['text' => 'Звук']],
            [['text' => 'Компьютер'], ['text' => 'Другое']],
        ];
        $this->vk->sendMessageWithKeyboard($userId, "🔧 Нужно ли оборудование? Выберите или введите список:", $buttons, true);
    }

    protected function processEquipment(int $userId, string $text, array $state): void
    {
        $text = trim($text);
        if (mb_strtolower($text) === 'отмена') {
            $state['equipment'] = null;
        } elseif (mb_strtolower($text) === 'нет') {
            $state['equipment'] = null;
        } else {
            $state['equipment'] = $text;
        }

        $state['step'] = 'tech_support';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $buttons = [
            [['text' => 'Да', 'color' => 'positive'], ['text' => 'Нет', 'color' => 'negative']],
        ];
        $this->vk->sendMessageWithKeyboard($userId, "👨‍💻 Нужен ли технический специалист для помощи в подключении оборудования?",
                                            $buttons, true);
    }

    protected function processTechSupport(int $userId, string $text, array $state): void
    {
        $text = mb_strtolower(trim($text));
        if ($text === 'да') {
            $state['is_tech_support'] = 1;
        } elseif ($text === 'нет') {
            $state['is_tech_support'] = 0;
        } else {
            $this->vk->sendMessage($userId, "❌ Ответьте «Да» или «Нет». Нужен ли тех. специалист?");
            return;
        }

        $state['step'] = 'comment';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $buttons = [
            [['text' => 'Нет', 'color' => 'negative']],
        ];
        $this->vk->sendMessageWithKeyboard($userId, "💬 Оставьте комментарий для администратора или нажмите «Нет»:", $buttons, true);
    }

    protected function processComment(int $userId, string $text, array $state): void
    {
        if (mb_strtolower(trim($text)) === 'нет') {
            $state['user_comment'] = null;
        } else {
            $state['user_comment'] = trim($text);
        }

        $state['step'] = 'confirm';
        Cache::put("vk_bot_state_{$userId}", $state, now()->addMinutes(30));

        $summary = "📋 Проверьте данные заявки:\n\n"
            . "👤 ФИО: {$state['name']}\n"
            . "🏛 Факультет: {$state['faculty']}\n"
            . "👥 Группа: {$state['group']}\n"
            . "📱 Телефон: {$state['phone']}\n"
            . "🏫 Аудитория: {$state['classroom_name']}\n"
            . "📅 Дата: {$state['date']}\n"
            . "⏰ Время: {$state['start_time']} – {$state['end_time']}\n"
            . "🎯 Цель: {$state['purpose']}\n"
            . "🔧 Оборудование: " . ($state['equipment'] ?: 'нет') . "\n"
            . "👨‍💻 Техподдержка: " . ($state['is_tech_support'] ? 'да' : 'нет') . "\n"
            . "💬 Комментарий: " . ($state['user_comment'] ?: 'нет') . "\n\n"
            . "Отправляем?";

        $buttons = [
            [['text' => '✅ Отправить', 'color' => 'positive'], ['text' => '❌ Отменить', 'color' => 'negative']],
        ];
        $this->vk->sendMessageWithKeyboard($userId, $summary, $buttons, true);
    }

    protected function processConfirm(int $userId, string $text, array $state): void
    {
        $text = mb_strtolower(trim($text));
        if ($text === 'отменить' || $text === '❌ отменить') {
            Cache::forget("vk_bot_state_{$userId}");
            $this->vk->sendMessage($userId, "🚫 Заявка отменена.");
            return;
        }

        if ($text !== 'отправить' && $text !== '✅ отправить') {
            $this->vk->sendMessage($userId, "Пожалуйста, выберите «Отправить» или «Отменить».");
            return;
        }

        $booking = Booking::create([
            'classroom_id'    => $state['classroom_id'],
            'date'            => $state['date'],
            'start_time'      => $state['start_time'],
            'end_time'        => $state['end_time'],
            'purpose'         => $state['purpose'],
            'name'            => $state['name'],
            'faculty'         => $state['faculty'],
            'group'           => $state['group'],
            'phone'           => $state['phone'],
            'equipment'       => $state['equipment'],
            'is_tech_support' => $state['is_tech_support'],
            'user_comment'    => $state['user_comment'],
            'vk_link'         => "https://vk.com/id{$userId}",
            'vk_user_id'      => $userId,
            'status'          => 'pending',
        ]);

        $booking->load('classroom');

        try {
            app(VkNotificationService::class)->notifyBookingCreated($booking, true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'VK notification create booking error: ' . $e->getMessage()
            );
        }

        Cache::forget("vk_bot_state_{$userId}");

        $this->vk->sendMessage($userId, "✅ Заявка успешно создана! Ожидайте подтверждения администратора. "
            . "О статусе заявки пришлём уведомление.");
    }
}