<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function providerCategories()
    {
        return $this->belongsToMany(ProviderCategory::class);
    }
    public function availableHours()
    {
        return $this->hasMany(AvailableHours::class);
    }
    public function providerVerificationDocuments()
    {
        return $this->hasMany(ProviderVerificationDocument::class);
    }
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
    

}
