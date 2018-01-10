<?php

namespace App\Models;

class Question extends BaseModel
{
    protected $table = "questions";
    protected $appends = ['short_content'];

    public function answers()
    {
        return $this->hasMany('App\Models\Answer', 'question_id', 'id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function forum()
    {
        return $this->hasOne('App\Models\Forum', 'id', 'forum_id');
    }

    public function getShortContentAttribute()
    {
        return getShareContent($this->attributes['content']);
    }

    public function getAnswerCountAttribute()
    {
        return Answer::where('question_id', $this->id)->count();
    }
}
