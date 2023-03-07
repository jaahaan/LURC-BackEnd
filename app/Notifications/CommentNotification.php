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
class CommentNotification extends Notification
{
    use Queueable;
    protected $user;
    protected $post;
    protected $comment_id;
    protected $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Post $post, $comment_id, $msg)
    {
        $this->user = $user;
        $this->post = $post;
        $this->comment_id = $comment_id;
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
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_image' => $this->user->image,
            'user_slug' => $this->user->slug,
            'post_title' => $this->post->title,
            'post_type' => $this->post->type,
            'post_slug' => $this->post->slug,
            'comment_id' => $this->comment_id,
            'msg' => $this->msg,
            'isRequest' => false
        ];
    }
}
