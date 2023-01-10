<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
    ];

    public function like(){
        return $this->hasOne('App\Models\CommentLike','comment_id' ,'id')->select('comment_id', DB::raw('COUNT(user_id) AS comment_like_count'))->groupBy('comment_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function post(){
        return $this->belongsTo('App\Models\Post');
    }
    public function replyCount(){
        return $this->hasOne('App\Models\CommentReply','comment_id' ,'id')->select('comment_id', DB::raw('COUNT(user_id) AS comment_reply_count'))->groupBy('comment_id');
    }
}
