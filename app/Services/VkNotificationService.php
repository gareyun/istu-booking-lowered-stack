<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VkNotificationService
{
    private $accessToken;
    private $apiVersion = '5.131';

    public function __construct()
    {
        $this->accessToken = config('services.vk.access_token');
    }

    public function extractUserId(string $vkLink): ?string
    {
        $vkLink = trim($vkLink);
        $vkLink = ltrim($vkLink, '@');
        
        if (preg_match('/vk\.com\/(.+)/', $vkLink, $matches)) {
            $identifier = trim($matches[1], '/');
        } else {
            $identifier = $vkLink;
        }
        
        if (preg_match('/^id(\d+)$/', $identifier, $matches)) {
            return $matches[1];
        }
        
        if (is_numeric($identifier)) {
            return $identifier;
        }
        
        return $this->resolveScreenName($identifier);
    }

    private function resolveScreenName(string $screenName): ?string
    {
        try {
            $response = Http::get('https://api.vk.com/method/utils.resolveScreenName', [
                'screen_name' => $screenName,
                'access_token' => $this->accessToken,
                'v' => $this->apiVersion,
            ]);

            $data = $response->json();
            
            if (isset($data['response']['object_id'])) {
                return (string) $data['response']['object_id'];
            }
            
            Log::warning('VK: Не удалось разрешить screen_name', [
                'screen_name' => $screenName,
                'response' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('VK resolveScreenName error: ' . $e->getMessage());
        }
        
        return null;
    }

    public function sendMessage(string $userId, string $message): bool
    {
        try {
            $randomId = random_int(100000, 999999);
            
            $response = Http::get('https://api.vk.com/method/messages.send', [
                'user_id' => (int) $userId,
                'message' => $message,
                'random_id' => $randomId,
                'access_token' => $this->accessToken,
                'v' => $this->apiVersion,
            ]);

            $data = $response->json();
            
            if (isset($data['error'])) {
                Log::error('VK send message error', [
                    'user_id' => $userId,
                    'error' => $data['error']
                ]);
                return false;
            }
            
            if (isset($data['response'])) {
                Log::info('VK message sent successfully', [
                    'user_id' => $userId,
                    'message_id' => $data['response']
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('VK sendMessage exception: ' . $e->getMessage());
            return false;
        }
    }

    public function notifyStatusChange($booking): bool
    {
        if (empty($booking->vk_link)) {
            Log::info('VK notification skipped: no vk_link');
            return false;
        }

        $userId = $this->extractUserId($booking->vk_link);
        
        if (!$userId) {
            Log::warning('VK: Не удалось извлечь ID пользователя', [
                'vk_link' => $booking->vk_link
            ]);
            return false;
        }

        $statusEmoji = match($booking->status) {
            'approved' => '✅',
            'rejected' => '❌',
            'cancelled' => '❌',
            default => '📋'
        };

        $statusText = match($booking->status) {
            'approved' => 'ОДОБРЕНА',
            'rejected' => 'ОТКЛОНЕНА',
            'cancelled' => 'ОТМЕНЕНА',
            default => 'ОБНОВЛЁН'
        };

        $message = "{$statusEmoji} Статус вашей заявки изменён!\n\n";
        $message .= "📋 Статус: {$statusText}\n";
        $message .= "🏫 Аудитория: {$booking->classroom->room}\n";
        $message .= "📅 Дата: {$booking->date}\n";
        $message .= "⏰ Время: {$booking->start_time} - {$booking->end_time}\n";
        $message .= "🎯 Цель: {$booking->purpose}\n";

        if (!empty($booking->admin_comment)) {
            $message .= "\n💬 Комментарий администратора:\n{$booking->admin_comment}";
        }

        if ($booking->status === 'approved') {
            $message .= "\n\nПравила бронирования:\n";
            $message .= "- при использовании танцевального зала обязательна сменная обувь\n";
            $message .= "- необходимо поддерживать чистоту после мероприятия\n";
            $message .= "- мебель должна быть возвращена на свои места";
        } elseif ($booking->status === 'rejected') {
            $message .= "\n\nВы можете подать новую заявку на другую дату или аудиторию.";
        } elseif ($booking->status === 'cancelled') {
            $message .= "\n\nБронь была отменена администратором. Вы можете подать новую заявку.";
        }

        return $this->sendMessage($userId, $message);
    }

    public function notifyBookingCreated($booking, bool $skipVkBot = false): bool
    {
        if ($skipVkBot && $booking->vk_user_id) {
            return false;
        }

        if (empty($booking->vk_link)) {
            Log::info('VK notification skipped: no vk_link');
            return false;
        }

        $userId = $this->extractUserId($booking->vk_link);

        if (!$userId) {
            Log::warning('VK: Не удалось извлечь ID пользователя', [
                'vk_link' => $booking->vk_link
            ]);

            return false;
        }

        $message = "📨 Ваша заявка на бронирование получена!\n\n";

        $message .= "🏫 Аудитория: {$booking->classroom->room}\n";
        $message .= "📅 Дата: {$booking->date}\n";
        $message .= "⏰ Время: {$booking->start_time} - {$booking->end_time}\n";
        $message .= "🎯 Цель: {$booking->purpose}\n";

        if (!empty($booking->equipment)) {
            $message .= "🔧 Оборудование: {$booking->equipment}\n";
        }

        $message .= "👨‍💻 Тех. специалист: "
            . ($booking->is_tech_support ? 'нужен' : 'не нужен') . "\n";

        if (!empty($booking->user_comment)) {
            $message .= "💬 Комментарий: {$booking->user_comment}\n";
        }

        $message .= "\n⏳ Сейчас заявка находится на рассмотрении администратора.";
        $message .= "\nО результате рассмотрения придёт отдельное уведомление.";

        return $this->sendMessage($userId, $message);
    }
}