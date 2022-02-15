<?php

namespace App;

use App\Libraries\Notifications\NewRefer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mockery\Undefined;

class Session extends Model
{
    protected $appends = ['state', 'is_referred', 'referral_status', 'used_seconds'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
	public function sessionCall()
    {
        return $this->hasOne(SessionCall::class);
    }
    public function getReferralStatusAttribute()
    {
        if(!$this->referral)
        {
            return Session::SESSION_REFERRAL_STATUS_NOT_REFERRED;
        }
        if($this->referral->surveyed_at == null)
        {
            return Session::SESSION_REFERRAL_STATUS_REFERRED;
        }
        if($this->referral->refund_confirmed == true)
        {
            return Session::SESSION_REFERRAL_STATUS_CONFIRMED;
        }
        if($this->referral->refund_confirmed == false)
        {
            return Session::SESSION_REFERRAL_STATUS_REJECTED;
        }
    }
    function getUsedSecondsAttribute()
    {
        return $this->duration * 60 - SessionCall::calculateMaxDuration($this->id);
    }
    public function getStateAttribute()
    {
        if ($this->accepted == null && $this->ended == null) {
            return Session::SESSION_STATE_REQUESTED;
          }
          if ($this->accepted != null && $this->started == null && $this->ended == null) {
            return Session::SESSION_STATE_RESERVED;
          }
          if ($this->started != null && $this->ended == null) {
            return Session::SESSION_STATE_ACTIVE;
          }
          if ($this->started != null && $this->ended != null) {
            return Session::SESSION_STATE_ENDED;
          }
          if ($this->accepted != null && $this->started == null && $this->ended != null) {
            return Session::SESSION_STATE_CANCELED;
          }
          if ($this->accepted == null && $this->ended != null) {
            return Session::SESSION_STATE_REJECTED;
          }
    }
    public function referral()
    {
        return $this->hasOne(SessionReferral::class);
    }
    public function getIsReferredAttribute()
    {
        if($this->referral)
        {
            return true;
        }
        return false;
    }
    public function refer($referNote, $referrer)
    {

        $referral = new SessionReferral();
        $referral->note = $referNote;
        $referral->referrer_id = $referrer->id;
        $referral->session_id = $this->id;
        $referral->save();
        if($this->ended == null)
        {
            $this->ended = Carbon::now();
        }
        $this->save();
        $supervisorArray = [];
        $provider = $this->provider;
        $providerCategoryIds = array_map(function($cat){
            return $cat['id'];
        },$provider->providerCategories()->get()->toArray() );
        $supervisors = Provider::where('is_supervisor', true)
            ->whereHas('providerCategories',function($query) use($providerCategoryIds){
                $query->whereIn('provider_categories.id', $providerCategoryIds);
            })->get();
        foreach($supervisors as $supervisor)
        {
            $supervisor->user->notify(new NewRefer(json_encode($this)));
        }

    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'related_id')->where('related_type', 1);
    }
    public function subscribeToEachOther()
    {
        $userTopic = $this->user->createTopic();
        $providerTopic = $this->provider->user->createTopic();
        $this->user->subscribe($providerTopic->id);
        $this->provider->user->subscribe($userTopic->id);
    }
    public function unsubscribeFromEachOther()
    {
        $userTopic = $this->user->createTopic();
        $providerTopic = $this->provider->user->createTopic();
        $this->user->unsubscribe($providerTopic->id);
        $this->provider->user->unsubscribe($userTopic->id);
    }
    public function request($userId, $reservedFrom,$reservedTo, $providerId,
        $chatTopicName, $duration, $type, $timingType)
    {
        $this->user_id = $userId;
        $this->provider_id = $providerId;
        $this->chat_topic_name = $chatTopicName;
        $this->reserved_from = $reservedFrom;
        $this->reserved_to = $reservedTo;
        $this->type = $type;
        $this->timing_type = $timingType;
        if ($this->type == 1) {
            $this->per_minute_fee = Provider::find($providerId)->per_minute_text_fee;
        } else {
            $this->per_minute_fee = Provider::find($providerId)->per_minute_call_fee;
        }

        $this->duration = $duration;
        $invoice = $this->createInvoice();

        $this->save();
        $this->invoice()->save($invoice);


    }
    public function createInvoice()
    {
        $invoice = new Invoice();
        $invoice->created_at = Carbon::now();
        $invoice->is_final = false;
        $invoice->is_pre_invoice = true;
        $invoice->amount = $this->per_minute_fee * $this->duration * -1;
        $invoice->user_id = auth()->user()->id;
        $invoice->related_type =  1;
        return $invoice;
    }
    public function finalCost()
    {
        $perMinuteFee = $this->per_minute_fee;
        $seconds = 0;
        if($this->type ==Session::SESSION_TYPE_TEXT )
        {
            $beginDate = Carbon::parse($this->started);
            $endDate = Carbon::parse($this->ended);
            $seconds = $endDate->diffInSeconds($beginDate);
        }
        else if($this->type ==Session::SESSION_TYPE_CALL)
        {
            $seconds = $this->used_seconds;
        }
        return floor($seconds / 60 * $perMinuteFee);

    }
    public  const SESSION_TYPE_TEXT = 1;
    public  const SESSION_TYPE_CALL = 2;
    public  const SESSION_TIMING_TYPE_IMMEDIATE = 1;
    public  const SESSION_TIMING_TYPE_RESERVATION = 0;
    public const SESSION_STATE_REQUESTED = 0;
    public const SESSION_STATE_RESERVED = 1;
    public const SESSION_STATE_ACTIVE = 2;
    public const SESSION_STATE_ENDED = 3;
    public const SESSION_STATE_REJECTED = 4;
    public const SESSION_STATE_CANCELED = 5;

    public const SESSION_REFERRAL_STATUS_NOT_REFERRED = 0;
    public const SESSION_REFERRAL_STATUS_REFERRED = 1;
    public const SESSION_REFERRAL_STATUS_CONFIRMED = 2;
    public const SESSION_REFERRAL_STATUS_REJECTED = 3;
}
