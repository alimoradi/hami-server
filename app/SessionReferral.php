<?php

namespace App;

use App\Libraries\Notifications\ReferralResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SessionReferral extends Model
{
    protected $appends = ['is_referred_by_user'];
    public function session()
    {
        return $this->belongsTo(Session::class);
    }
    public function surveyor()
    {
        return $this->belongsTo(Provider::class,'surveyor_id');
    }
    public function referrer()
    {
        return $this->belongsTo(User::class,'referrer_id');
    }
    public function getIsReferredByUserAttribute()
    {
        if($this->referrer->role_id == User::PROVIDER_ROLE_ID)
        {
            return false;
        }
        return true;
    }
    public function reject($surveyorId)
    {
        $this->refund_confirmed = false;
        $this->surveyed_at = Carbon::now();
        $this->surveyor_id = $surveyorId;
        $this->save();
        $this->session->user->notify(new ReferralResult($this->session));
        return $this;
    }
    public function confirm($surveyorId)
    {
        $this->refund_confirmed = true;
        $this->surveyed_at = Carbon::now();
        $this->surveyor_id = $surveyorId;
        $this->save();
        $this->session->user->notify(new ReferralResult($this->session));
        return $this;
    }
}
