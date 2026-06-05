<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Exception;
use Carbon\Carbon;
use RuntimeException;

class GoogleCalendarService
{
    public function client()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path(config('services.google_service_account.path')));
        $client->addScope(Google_Service_Calendar::CALENDAR);

        return $client;
    }

    public static function resolveCalendarId($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/[?&]src=([^&]+)/', $value, $matches)) {
            return urldecode($matches[1]);
        }

        if (preg_match('/[?&]cid=([^&]+)/', $value, $matches)) {
            $decoded = base64_decode(urldecode($matches[1]), true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $value;
    }

    public function createEvent($booking)
    {
        $calendarId = static::resolveCalendarId($booking->classroom->google_calendar_id ?? null);

        if (empty($calendarId)) {
            throw new RuntimeException(
                'У аудитории «' . ($booking->classroom->room ?? '?') . '» не указан Google Calendar ID.'
            );
        }

        $service = new Google_Service_Calendar($this->client());

        $date = Carbon::createFromFormat('d.m.Y', $booking->date)->format('Y-m-d');

        $description = trim(implode("\n", array_filter([
            $booking->user_comment,
            $booking->admin_comment,
        ])));

        $event = new Google_Service_Calendar_Event([
            'summary' => $booking->purpose ?? 'Бронирование',
            'description' => $description,
            'start' => [
                'dateTime' => $date . 'T' . $booking->start_time . ':00',
                'timeZone' => 'Europe/Samara',
            ],
            'end' => [
                'dateTime' => $date . 'T' . $booking->end_time . ':00',
                'timeZone' => 'Europe/Samara',
            ],
        ]);

        try {
            $event = $service->events->insert($calendarId, $event);
        } catch (Google_Service_Exception $e) {
            if ((int) $e->getCode() === 404) {
                throw new RuntimeException(
                    'Календарь Google не найден или сервисному аккаунту не выдан доступ. '
                    . 'Проверьте ID календаря аудитории и права для booking-helper@integral-istu.iam.gserviceaccount.com.',
                    0,
                    $e
                );
            }

            throw $e;
        }

        return $event->id;
    }

    public function deleteEvent($booking): void
    {
        $calendarId = static::resolveCalendarId($booking->classroom->google_calendar_id ?? null);

        if (empty($booking->google_event_id) || empty($calendarId)) {
            return;
        }

        $service = new Google_Service_Calendar($this->client());

        try {
            $service->events->delete($calendarId, $booking->google_event_id);
        } catch (Google_Service_Exception $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }
    }
}
