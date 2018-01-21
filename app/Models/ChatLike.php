<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ChatLike extends BaseModel
{
    protected $table = "shuoshuo_upvotes";
    use SoftDeletes;
    protected $guarded = [];
    protected $fillable = [
        'user_id',
        'shuoshuo_id',
    ];

}
