<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\{
    User, Post
};
class MessageNotification extends Notification
{
    use Queueable;
    protected $user;
    protected $room_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $room_id)
    {
        $this->user = $user;
        $this->room_id = $room_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];    }


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
            'room_id' => $this->room_id,
            'msg' => 'msg'
        ];
    }
}
