<?php

namespace Modules\Home\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Http\Controllers\Controller;
use App\Models\DB1\SysUserFbToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Home\Notifications\Greeting;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        $breadcrumbs = [
            new Breadcrumbs('Notifikasi', route('notifications')),
        ];

        $parser = [
            'breadcrumbs' => $breadcrumbs,
            'notifications' => $notifications,
        ];

        return view('home::notification.index', $parser);
    }

    public function test()
    {
        $user = Auth::user();
        $user->notify(new Greeting('Test Notification', 'This is a test notification', route('notifications'), ['database', 'mail', 'fcm']));

        return responseJSON('Test notification sent', [
            'notification_sent' => true,
        ]);
    }

    /**
     * Open and mark a specific notification as read
     */
    public function open($id)
    {
        try {
            $notificationId = decrypt($id);
            $notification = Auth::user()->notifications()->find($notificationId);

            if (! $notification) {
                return redirect()->route('notifications')->with('error', 'Notifikasi tidak ditemukan');
            }

            // Mark as read if unread
            if ($notification->unread()) {
                $notification->markAsRead();
            }

            // Redirect to the notification's intended URL if available
            if (isset($notification->data['url'])) {
                return redirect($notification->data['url']);
            }

            return redirect()->route('notifications')->with('success', 'Notifikasi dibuka');

        } catch (\Exception $e) {
            return redirect()->route('notifications')->with('error', 'Notifikasi tidak valid');
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca');
    }

    /**
     * Store or update Firebase Cloud Messaging token
     */
    public function storeToken(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $token = $request->input('token');

            if (! $token) {
                return responseJSON('Token is required', [], 400);
            }

            $userAgent = $request->userAgent();
            $ip = $request->ip();
            $userId = Auth::id();

            // Use updateOrCreate to avoid duplicate tokens from same device
            SysUserFbToken::updateOrCreate(
                [
                    'user_id' => $userId,
                    'token' => $token,
                    'agent' => $userAgent,
                ],
                [
                    'ip' => $ip,
                ]
            );

            return responseJSON('Token stored successfully', [
                'token_stored' => true,
            ]);

        } catch (\Exception $e) {
            return responseJSON('Failed to store token', [], 500);
        }
    }
}
