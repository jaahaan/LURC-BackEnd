<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationChat extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_id',
        'from_id',
        'to_id',
        'msg',
        'image',
        'is_seen'
    ];
    public function fromUser(){
        return $this->belongsTo(User::class, 'from_id');
    }

    public function toUser(){
        return $this->belongsTo(User::class, 'to_id');
    }
}
