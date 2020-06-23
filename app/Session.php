<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'related_id')->where('related_type', 1);
    }
}
