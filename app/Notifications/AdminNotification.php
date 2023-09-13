<?php

namespace App\Notifications;

use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AdminNotification extends Notification
{
    protected $message, $link;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $link)
    {
        $this->message  = $message;
        $this->link     = $link;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message'   => $this->message,
            'link'      => $this->link,
            'auth_name' => auth()->check() ? auth()->user()->name : 'Selopia'
        ];
    }
}
