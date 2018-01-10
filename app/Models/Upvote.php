<?php

namespace App\Models;

class Upvote extends BaseModel
{
    protected $fillable = ['user_id', 'post_id']; //可以注入的属性
}
