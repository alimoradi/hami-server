<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'subscriptions');
    }
    public  const TOPIC_TYPE_PEER = 1;
    public  const TOPIC_TYPE_SESSION = 2;
}
