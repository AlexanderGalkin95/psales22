<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class Notify extends Notification implements ShouldQueue
{
  use Queueable;

  public function __construct() {
    $this->connection = 'notification';
  }

    /**
     * Get the notification's delivery channels.
     *
     * @param $notifiable
     * @return array
     */
  public function via($notifiable): array
  {
      return ['mail'];
  }
}
