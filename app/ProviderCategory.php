<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderCategory extends Model
{
    public function providers()
    {
        return $this->belongsToMany(Provider::class);
    }
}
