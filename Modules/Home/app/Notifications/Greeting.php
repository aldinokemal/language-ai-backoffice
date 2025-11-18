<?php

namespace Modules\Home\Notifications;

use App\Models\DB1\SysUser;
use App\Notifications\Messages\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Greeting extends Notification
{
    use Queueable;

    protected $title;

    protected $message;

    protected $actionUrl;

    protected $via;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $actionUrl, $via = ['database', 'mail'])
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->via = $via;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(SysUser $notifiable): array
    {
        return $this->via;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line($this->message)
            ->action('View Details', $this->actionUrl)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->title($this->title)
            ->body($this->message)
            ->data([
                'action_url' => $this->actionUrl,
                'type' => 'greeting',
                'timestamp' => now()->toIso8601String(),
            ])
            ->clickAction($this->actionUrl)
            ->priority('high');
    }
}
