<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
        'user_id',
        'institute',
        'degree',
        'fieldOfStudy',
        'start_date',
        'end_date',
        'grade',
        'activities',
    ];
    use HasFactory;
}
