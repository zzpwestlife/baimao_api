<?php

namespace App\Models;

class Answer extends BaseModel
{
    protected $table = "answers";

    public function question()
    {
        return $this->belongsTo('App\Models\Question', 'question_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function forum()
    {
        return $this->hasOne('App\Models\Forum', 'id', 'forum_id');
    }
}
