<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_id',
        'to_id',
        'last_msg'
    ];
    public function fromUser(){
        return $this->belongsTo(User::class, 'from_id');
    }

    public function toUser(){
        return $this->belongsTo(User::class, 'to_id');
    }
}
