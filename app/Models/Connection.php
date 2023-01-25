<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;
    protected $fillable = [
        'sent_request_user',
        'received_request_user',
    ];
    public function user1(){
        return $this->belongsTo(User::class, 'sent_request_user');
    }

    public function user2(){
        return $this->belongsTo(User::class, 'received_request_user');
    }
}
