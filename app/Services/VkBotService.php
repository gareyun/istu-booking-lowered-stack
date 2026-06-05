<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VkBotService
{
    protected string $token;
    protected string $version = '5.199';

    public function __construct()
    {
        $this->token = config('services.vk.access_token');
    }

    public function api($method, $params = [])
    {
        return Http::get(
            "https://api.vk.com/method/{$method}",
            array_merge($params, [
                'access_token' => $this->token,
                'v' => $this->version,
            ])
        )->json();
    }

    public function sendMessage($userId, $message)
    {
        return $this->api('messages.send', [
            'user_id' => $userId,
            'message' => $message,
            'random_id' => random_int(1, PHP_INT_MAX),
        ]);
    }

    public function sendMessageWithKeyboard($userId, $message, array $buttons, bool $oneTime = false)
    {
        $keyboard = [
            'one_time' => $oneTime,
            'buttons' => array_map(function ($row) {
                return array_map(function ($btn) {
                    $action = [
                        'type' => 'text',
                        'label' => $btn['text'],
                    ];
                    $button = ['action' => $action];
                    if (isset($btn['color'])) {
                        $button['color'] = $btn['color'];
                    }
                    return $button;
                }, $row);
            }, $buttons),
        ];

        return $this->api('messages.send', [
            'user_id' => $userId,
            'message' => $message,
            'random_id' => random_int(1, PHP_INT_MAX),
            'keyboard' => json_encode($keyboard),
        ]);
    }
}