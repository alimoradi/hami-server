<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicQuestionAndAnswers extends Model
{
    protected $appends = ['answer_count'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function answers()
    {
        return $this->hasMany(PublicQuestionAndAnswers::class, 'question_id');
    }
    public function question()
    {
        return $this->belongsTo(PublicQuestionAndAnswers::class, 'question_id');
    }
    public function getAnswerCountAttribute()
    {
        
        return $this->answers->count();
    }
}
