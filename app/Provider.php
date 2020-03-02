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
    public function providerCategory()
    {
        return $this->belongsTo(ProviderCategory::class);
    }
}
