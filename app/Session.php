<?php

namespace App;

use App\Libraries\Notifications\NewRefer;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $appends = ['state'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
    public function getStateAttribute()
    {
        if ($this->accepted == null && $this->ended == null) {
            return Session::SESSION_STATE_REQUESTED;
          }
          if ($this->accepted != null && $this->started == null) {
            return Session::SESSION_STATE_RESERVED;
          }
          if ($this->started != null && $this->ended == null) {
            return Session::SESSION_STATE_ACTIVE;
          }
          if ($this->accepted != null && $this->ended != null) {
            return Session::SESSION_STATE_ENDED;
          }
          if ($this->accepted == null && $this->ended != null) {
            return Session::SESSION_STATE_REJECTED;
          }
    }
    public function refer($referNote)
    {
        $this->is_referred = true;
        $this->refer_note = $referNote;
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
            $supervisor->user->notify(new NewRefer($this));
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
    public  const SESSION_TYPE_TEXT = 1;
    public  const SESSION_TYPE_CALL = 2;
    public  const SESSION_TIMING_TYPE_IMMEDIATE = 1;
    public  const SESSION_TIMING_TYPE_RESERVATION = 0;
    public const SESSION_STATE_REQUESTED = 0;
    public const SESSION_STATE_RESERVED = 1;
    public const SESSION_STATE_ACTIVE = 2;
    public const SESSION_STATE_ENDED = 3;
    public const SESSION_STATE_REJECTED = 4;
}
