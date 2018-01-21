<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ShuoshuoUpvote extends BaseModel
{
    use SoftDeletes;
    protected $guarded = [];
    protected $fillable = [
        'user_id',
        'shuoshuo_id',
    ];
    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function shuoshuo() {
        return $this->belongsTo('App\Models\Shuoshuo');
    }
}
