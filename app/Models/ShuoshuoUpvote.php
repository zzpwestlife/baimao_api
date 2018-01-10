<?php

namespace App\Models;

class ShuoshuoUpvote extends BaseModel
{
    protected $guarded = [];
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function shuoshuo() {
        return $this->belongsTo('App\Models\Shuoshuo');
    }
}
