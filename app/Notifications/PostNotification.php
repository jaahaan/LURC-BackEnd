<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Auth;
use App\Models\{
    User, Post
};
class PostNotification extends Notification implements ShouldBroadcast
{
    use Queueable;
    protected $user;
    protected $post;
    protected $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Post $post, $msg, )
    {
        $this->user = $user;
        $this->post = $post;
        $this->msg = $msg;
        // $this->notificationCount = $notificationCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['database', 'broadcast'];
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
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_image' => $this->user->image,
            'user_slug' => $this->user->slug,
            'post_title' => $this->post->title,
            'post_type' => $this->post->type,
            'post_slug' => $this->post->slug,
            'msg' => $this->msg,
            'isRequest' => false
        ];
    }
    public function toBroadcast($notifiable){

        $notification = [
            "data" => [
                "user_name" => $this->user->name,
                // "notificationCount" => $this->notificationCount,
                "post_title" => $this->post->title,
            ]
        ];

        return new BroadcastMessage([
            'notification' => $notification
        ]);
    }
}
