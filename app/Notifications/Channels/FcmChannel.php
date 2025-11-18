<?php

namespace App\Notifications\Channels;

use App\Models\DB1\SysUser;
use App\Notifications\Messages\FcmMessage;
use App\Services\FcmService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send the given notification.
     */
    public function send(SysUser $notifiable, Notification $notification): void
    {
        // Check if the notification has a toFcm method
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        // Get the FCM message from the notification
        $message = $notification->toFcm($notifiable);

        // If no message is returned, skip sending
        if (! $message instanceof FcmMessage) {
            return;
        }

        // Get all FCM tokens for this user
        $tokens = $notifiable->fbTokens()->pluck('token')->toArray();

        // If no tokens found, log and return
        if (empty($tokens)) {
            if (config('fcm.logging.enabled')) {
                Log::channel(config('fcm.logging.channel'))
                    ->warning('No FCM tokens found for user', [
                        'user_id' => $notifiable->id,
                        'notification' => get_class($notification),
                    ]);
            }

            return;
        }

        // Build notification and data payloads
        $notificationPayload = $message->getNotification();
        $dataPayload = $message->getData();

        // Send to all user's tokens
        $results = $this->fcmService->sendToTokens($tokens, $notificationPayload, $dataPayload);

        // Log results if logging is enabled
        if (config('fcm.logging.enabled')) {
            $successCount = count(array_filter($results));
            $failureCount = count($tokens) - $successCount;

            Log::channel(config('fcm.logging.channel'))->info('FCM notification batch sent', [
                'user_id' => $notifiable->id,
                'notification' => get_class($notification),
                'total_tokens' => count($tokens),
                'success' => $successCount,
                'failed' => $failureCount,
            ]);
        }
    }
}
