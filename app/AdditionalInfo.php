<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Provider;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalInfo extends Model
{
    protected $table = 'additional_info';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
