<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LmsAlertNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $url;
    public $icon;
    public $colorClass;

    public function __construct($title, $message, $url, $icon = 'fas fa-bell', $colorClass = 'text-[#a52a2a]')
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
        $this->colorClass = $colorClass;
    }

    // Tell Laravel to save this in the database
    public function via($notifiable)
    {
        return ['database'];
    }

    // Format the JSON data stored in the database
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
            'colorClass' => $this->colorClass
        ];
    }
}