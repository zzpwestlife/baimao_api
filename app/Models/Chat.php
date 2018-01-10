<?php

namespace App\Models;

use App\ShuoshuoComment;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Chat extends BaseModel implements Transformable
{
    use TransformableTrait;

    protected $table = "shuoshuos";


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function forum()
    {
        return $this->belongsTo('App\Models\Forum');
    }

    public function comment($user_id)
    {
        return $this->hasOne('App\Models\ShuoshuoComment')->where('user_id', $user_id);
    }

    public function shuoshuocomments()
    {
        return $this->hasMany('App\Models\ShuoshuoComment', 'shuoshuo_id', 'id');
    }

    public function upvote($user_id)
    {
        return $this->hasOne('App\Models\ShuoshuoUpvote')->where('user_id', $user_id);
    }

    public function shuoshuoupvotes()
    {
        return $this->hasMany('App\Models\ShuoshuoUpvote', 'shuoshuo_id', 'id');

    }
}

