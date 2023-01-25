<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\User;
class ConnectionNotification extends Notification
{
    use Queueable;
    protected $user;
    protected $connection_id;
    protected $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $connection_id, $msg)
    {
        $this->user = $user;
        $this->connection_id = $connection_id;
        $this->msg = $msg;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_image' => $this->user->image,
            'user_slug' => $this->user->slug,
            'connection_id' => $this->connection_id,
            'msg' => $this->msg,
            'isRequest' => true
        ];
    }
}
