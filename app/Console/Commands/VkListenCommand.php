<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VkBotService;
use App\Services\VkDialogService;

class VkListenCommand extends Command
{
    protected $signature = 'vk:listen';

    protected $description = 'VK Long Poll listener';

    public function handle(VkBotService $vk, VkDialogService $dialog)
    {
        $server = $vk->api('groups.getLongPollServer', [
            'group_id' => config('services.vk.community_id'),
        ]);

        if (isset($server['error'])) {
            $this->error('Long Poll error: ' . json_encode($server['error']));
            return 1;
        }

        $serverData = $server['response'];
        $ts = $serverData['ts'];

        $this->info('VK bot started. Waiting for messages...');

        while (true) {
            $response = file_get_contents(
                $serverData['server']
                . '?act=a_check'
                . '&key=' . $serverData['key']
                . '&ts=' . $ts
                . '&wait=25'
            );

            $data = json_decode($response, true);

            if (!isset($data['ts'])) {
                $this->warn('TS missing, reconnecting...');
                $server = $vk->api('groups.getLongPollServer', [
                    'group_id' => config('services.vk.community_id'),
                ]);
                if (isset($server['response'])) {
                    $serverData = $server['response'];
                    $ts = $serverData['ts'];
                }
                continue;
            }

            $ts = $data['ts'];

            if (!empty($data['updates'])) {
                foreach ($data['updates'] as $update) {
                    if ($update['type'] === 'message_new') {
                        $message = $update['object']['message'];
                        $userId = $message['from_id'];
                        $text = $message['text'] ?? '';

                        $this->info("New message from {$userId}: {$text}");

                        $dialog->handleMessage($userId, $text);
                    }
                }
            }
        }

        return 0;
    }
}