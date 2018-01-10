<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    protected $guarded = [];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'fullAvatarUrl'
    ];

    public function shuoshuos()
    {
        return $this->hasMany('App\Models\Shuoshuo', 'user_id', 'id');
    }

    public function experiences()
    {
        return $this->hasMany('App\Models\Experience', 'user_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany('App\Models\Question', 'user_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer', 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\File', 'user_id', 'id');
    }

    public function getFullAvatarUrlAttribute()
    {
        return DATA_URL . ((!empty($this->avatar_url)) ? $this->avatar_url : '/images/avatar/1.jpg');
    }


}
