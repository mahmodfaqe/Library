<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Telegram
{
    /**
     * Send a line to whoever looks after the library.
     *
     * Alerts are worth nothing if they arrive by a route nobody watches at two
     * in the morning, which is why this goes to a phone.
     *
     * It never throws. An alert that brings down the thing it was reporting on
     * would be worse than no alert, so a failure here is written to the log
     * and the caller carries on.
     */
    public static function send(string $message): bool
    {
        $token = config('services.telegram.token');
        $chat = config('services.telegram.chat');

        if (! $token || ! $chat) {
            // Not configured is not an error: a development machine has no
            // business messaging anyone.
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chat,
                    'text' => $message,
                    'disable_web_page_preview' => true,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram refused an alert.', ['status' => $response->status()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Could not reach Telegram.', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public static function configured(): bool
    {
        return (bool) (config('services.telegram.token') && config('services.telegram.chat'));
    }
}
