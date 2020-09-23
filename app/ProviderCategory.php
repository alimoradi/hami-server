<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderCategory extends Model
{
    public function providers()
    {
        return $this->belongsToMany(Provider::class);
    }
    public function supervisors()
    {
        return $this->providers()->where('is_supervisor', true)->get();
    }
}
