<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FavoriteProvider extends Model
{
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
