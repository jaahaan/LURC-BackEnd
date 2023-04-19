<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    use HasFactory;
    protected $fillable = [
        'type','notifiable_id', 'user_id', 'post_id', 'msg', 'comment_id', 'connection_id', 'isRequest', 'room_id', 'read_at', 'seen_at' 
    ];
    public function user(){
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }
    public function post(){
        return $this->belongsTo('App\Models\Post','post_id', 'id');
    }
}
