<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification
{
    use Queueable;

    public $client, $session, $name;

    /**
     * Create a new notification instance.
     */
    public function __construct($client, $session, $name)
    {
        $this->client = $client;
        $this->session = $session;
        $this->name = $name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Client Follow Up')
            ->greeting('Dear ' . $this->name)
            ->line('You have a client follow up today at '. Carbon::parse($this->session)->format('H:i') .'.')
            ->line('Client Information:')
            ->line('Name: ' . $this->client->name)
            ->line('Email: ' . $this->client->email)
            ->line('Phone No: '.$this->client->phone_no);
    }
}
