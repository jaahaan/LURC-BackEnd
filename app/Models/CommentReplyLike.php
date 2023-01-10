<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReplyLike extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'comment_reply_id',
    ];
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
