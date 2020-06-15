<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    //
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
