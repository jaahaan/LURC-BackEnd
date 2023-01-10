<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use DB;

class Post extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'user_id',
        'department_id',
        'type',
        'slug',
        'title',
        'abstract', 
        'url',
        'affiliation',
        'attachment',
        'start_date',
        'end_date',
        'isApproved',
        'approved_at',
    ];

    public function authors(){
        return $this->belongsToMany('App\Models\User','authors');
    }
    public function comments(){
        return $this->hasMany('App\Models\Comment','post_id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','user_id', 'id');
    }
    public function department()
    {
       return $this->belongsTo('App\Models\Department','department_id','id');
    }
    public function attachments(){
        return $this->hasMany('App\Models\Attachment','post_id');
    }
    public function vote(){
        return $this->hasOne('App\Models\Vote','post_id')->select('post_id', DB::raw('SUM(upVote) AS upVote'), DB::raw('SUM(downVote) AS downVote'))->groupBy('post_id');
    }

    public function like(){
        return $this->hasOne('App\Models\Like','post_id' ,'id')->select('post_id', DB::raw('COUNT(user_id) AS like_count'))->groupBy('post_id');
    }

    public function read()
    {      
      return $this->hasOne('App\Models\Read','post_id' ,'id')->select('post_id', DB::raw('COUNT(user_id) AS read_count'))->groupBy('post_id');
    }
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }


    // public function authors(){
    //     return $this->hasMany('App\Models\Author','post_id');
    // }
    // public function attachments(){
    //     return $this->hasMany('App\Models\Attachment','post_id');
    // }
}
