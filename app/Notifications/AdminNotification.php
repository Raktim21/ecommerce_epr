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
    protected $message, $model, $model_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $model, $model_id)
    {
        $this->message  = $message;
        $this->model    = $model;
        $this->model_id = $model_id;
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
            'model'     => $this->model,
            'model_id'  => $this->model_id,
        ];
    }
}
