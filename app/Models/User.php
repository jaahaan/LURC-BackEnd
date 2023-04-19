<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Cviebrock\EloquentSluggable\Sluggable;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use Sluggable;

    public function getJWTIdentifier(){
        return $this->getKey();
    }
    public function getJWTCustomClaims(){
        return [];
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'slug',
        'password',
        'department',
        'department_id',
        'designation',
        'image',
        'about',
        'skills',
        'twoFactor',
        'honors_and_awards',
        'userType',
        'passwordToken',
        'isActive', 
        'token_expired_at',
    ];
    public function setPasswordAttribute($password)
    {
        if ($password !== null) {
            if (is_null(request()->bcrypt)) {
                $this->attributes['password'] = bcrypt($password);
            } else {
                $this->attributes['password'] = $password;
            }
        }
    }
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    // public function setSlugAttribute($name){
    //     $this->attributes['slug'] = $this->uniqueSlug($name);
    // }

    // private function uniqueSlug($name){
    //     $slug = Str::slug($name, '-');
    //     $count = User::where('slug', 'LIKE', "{$slug}%")->count();
    //     $newCount = $count > 0 ? ++$count : '';
    //     return $newCount > 0 ? "$slug-$newCount" : $slug;
    // }

    // public function setPasswordAttribute($password){
    //     $this->attributes['password'] = encrypt($password);
    // }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'education' => 'array',
        // 'skills' => 'array',
    ];


    public function department()
    {
       return $this->belongsTo('App\Models\Department','department_id','id');
    }

    public function user_skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills');
    }
    public function notification(){
        return $this->belongsToMany('App\Models\Notify','post_user_id');
    }
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'user_id');
    }
    public function connections()
    {
        return $this->hasMany(Connection::class);
    }

}
