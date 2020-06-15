<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
class User extends Authenticatable
{
    use Notifiable, HasApiTokens;


    public function findForPassport($username) {
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
    public function getAuthPassword() {
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
        ->whereHas('topic', function ($query)  {
            $query->where('type', '=', 1);
        })->with(['topic', 'topic.subscribers']);
    }
    public function sessionSubscriptions()
    {
        return $this->hasMany(Subscription::class)
        ->where('subscribed_at', '!=', null)
        ->where('unsubscribed_at', null)
        ->whereHas('topic', function ($query)  {
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
