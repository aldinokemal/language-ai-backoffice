<?php

namespace Modules\Home\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpUpdateEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public string $otp;

    public string $email;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp, string $email)
    {
        $this->otp = $otp;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Perubahan Email - '.config('app.name'))
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mengubah alamat email Anda ke: **'.$this->email.'**')
            ->line('Untuk menyelesaikan perubahan ini, silakan masukkan kode OTP berikut:')
            ->line('## **'.$this->otp.'**')
            ->line('Kode OTP ini akan kedaluwarsa dalam **5 menit**.')
            ->line('Jika Anda tidak melakukan permintaan ini, silakan abaikan email ini atau hubungi tim dukungan kami.')
            ->line('Terima kasih telah menggunakan '.config('app.name').'!')
            ->salutation('Salam,  
Tim '.config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'email_otp',
            'otp' => $this->otp,
            'email' => $this->email,
            'message' => 'Kode OTP untuk perubahan email telah dikirim',
        ];
    }
}
