<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;


    public function findForPassport($username)
    {
        return $this->where('phone', $username)->where('phone_verified_at', '<>', '')->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        //$hasher = new HSAUserHasher(); // Or whomever does your hashing

        //$result = $hasher->create_hash($password, $this->salt);
        //$hashedPassword = $result['password'];

        return $password == $this->password;
    }
    public function provider()
    {
        return $this->hasOne(Provider::class);
    }
    public function getAuthPassword()
    {
        return Hash::make($this->password);
    }
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
    public function checkRole()
    {
        return $this->role->name;
    }

    public function additionalInfo()
    {
        return $this->hasOne(AdditionalInfo::class);
    }
    public function p2pSubscriptions()
    {
        return $this->hasMany(Subscription::class)
            ->where('subscribed_at', '!=', null)
            ->where('unsubscribed_at', null)
            ->whereHas('topic', function ($query) {
                $query->where('type', Topic::TOPIC_TYPE_PEER);
            })->with(['topic', 'topic.subscribers']);
    }
    public function p2pPeers()
    {
        return User::whereHas('p2pSubscriptions', function ($query){
            $query->whereHas('topic.subscribers',function($query){
                $query->where('user_id' , '!=', $this->id);
            });
        })->get();
        
        
    }
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
    public function sessionSubscriptions()
    {
        return $this->hasMany(Subscription::class)
            ->where('subscribed_at', '!=', null)
            ->where('unsubscribed_at', null)
            ->whereHas('topic', function ($query) {
                $query->where('type', '=', 2);
            })->with(['topic', 'topic.subscribers']);
    }
    public function mustSubscriptions()
    {
        return $this->hasMany(Subscription::class)
            ->where('subscribed_at', null)
            ->where('unsubscribed_at', null)
            ->where('must_subscribe', true)
            ->with(['topic']);
    }
    
    public function createTopic()
    {
        $userTopic = Topic::where('name', $this->tinode_uid)->first();
        if($userTopic === null)
        {
            $userTopic = new Topic();
            $userTopic->name = $this->tinode_uid;
            $userTopic->type = Topic::TOPIC_TYPE_PEER;
            $userTopic->save();
        }
        return $userTopic;
    }
    public function subscribe($topicId)
    {
        $sub = Subscription::where('topic_id', $topicId)
            ->where('user_id', $this->id)
            ->where('unsubscribed_at', null)
            ->first();
        if ($sub != null) {
            $sub = $this->unsubscribe($topicId);
        }
        $sub = new Subscription();
        $sub->topic_id = $topicId;
        $sub->subscribed_at = Carbon::now();
        $sub->user_id = $this->id;
        $sub->save();
        return $sub;
    }
    public function unsubscribe($topicId)
    {
        $sub = Subscription::where('topic_id', $topicId)
            ->where('user_id', $this->id)
            ->where('unsubscribed_at', null)
            ->first();
        if ($sub === null) {
            $sub = $this->subscribe($topicId);
        }
        $sub->unsubscribed_at = Carbon::now();
        $sub->save();
        return $sub;
    }
    public function deposit($amount)
    {
        
        $invoice = new Invoice();
        $invoice->created_at = Carbon::now();
        $invoice->is_final = true;
        $invoice->related_type = 2;
        $invoice->is_pre_invoice = false;
        $invoice->amount = $amount;
        $invoice->user_id = $this->id;
        $invoice->related_id = 0;
        $invoice->save();
        return $invoice;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone_verified_at' => 'datetime',
    ];
}
