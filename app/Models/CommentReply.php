<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class CommentReply extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'post_id',
        'comment_id',
        'comment',
    ];

    public function like(){
        return $this->hasOne('App\Models\CommentReplyLike','comment_reply_id' ,'id')->select('comment_reply_id', DB::raw('COUNT(user_id) AS comment_reply_like_count'))->groupBy('comment_reply_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
