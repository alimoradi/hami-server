<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Provider;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderVerificationDocument extends Model
{

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
