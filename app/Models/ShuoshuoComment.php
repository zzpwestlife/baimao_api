<?php

namespace App\Models;

class ShuoshuoComment extends BaseModel
{
    protected $guarded = [];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function parent() {
        return $this->belongsTo('App\Models\ShuoshuoComment', 'parent_id');
    }

    public function shuoshuo() {
        return $this->belongsTo('App\Models\Shuoshuo');
    }
}
