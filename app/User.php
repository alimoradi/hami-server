<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    protected $appends = ['avatar_thumbnail', 'is_supervisor'];


    public function findForPassport($username)
    {
        return $this->where('phone', $username)->where('phone_verified_at', '<>', '')->first();
    }
    public function favoriteProviders()
    {
        return $this->belongsToMany(Provider::class, 'favorite_providers')->with(['user', 'providerCategories']);
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
        $topicIds = $this->p2pSubscriptions()->pluck('topic_id')->toArray() ;
        $uids =$this->livePeerTopics()->pluck('topics.name')->toArray();
        return User::whereIn('tinode_uid', $uids)->get();
    }
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'subscriptions');
    }
    public function livePeerTopics()
    {
        return $this->belongsToMany(Topic::class, 'subscriptions')
        ->where('type', Topic::TOPIC_TYPE_PEER)
        ->wherePivot('unsubscribed_at', '=', null)->distinct();
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
        if ($userTopic === null) {
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

        $payment = $this->requestDeposit($amount, "0000");
        $payment->verify("0000");
        return $payment->invoice;

    }
    public function requestDeposit($amount, $authorityCode)
    {
        $payment = new Payment();
        $payment->user_id = $this->id;
        $payment->amount = $amount;
        $payment->authority_code = $authorityCode;
        $payment->save();
        $payment->createInvoice();
        return $payment;
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function getAvatarThumbnailAttribute()
    {
        $directory = 'public/';
        if($this->avatar == null)
        {
            return null;
        }
        if(!Storage::exists($directory.$this->avatar))
        {
            return null;
        }
        if(Storage::exists($directory.User::AVATAR_THUMBNAIL_PREFIX.$this->avatar))
        {
            return User::AVATAR_THUMBNAIL_PREFIX.$this->avatar;
        }
        $imageUrl = Storage::path($directory) .$this->avatar;
        $temp = tmpfile();
        $metaDatas = stream_get_meta_data($temp);
        $tmpFilename = $metaDatas['uri'];
        fclose($temp);
        file_put_contents($tmpFilename, file_get_contents($imageUrl));
        $this->saveAvatarThumbnail($tmpFilename, 'public',$this->avatar);
        return User::AVATAR_THUMBNAIL_PREFIX.$this->avatar;
    }
    public function saveAvatar($image)
    {
        $size = $image->getsize();
        $mime = $image->getMimeType();
        $extension = $image->extension();
        $directory = 'public';
        $name = uniqid("", true) . '.' . $extension;
        $width = getimagesize($image)[0];
        $height = getimagesize($image)[1];
        Storage::putFileAs($directory, $image, $name);
        $this->saveAvatarThumbnail($image->path(), $directory, $name);
        $this->avatar = $name;
        $this->save();
        return  [
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'mime_type' => $mime,
            'size' => $size,
            'extension' => $extension
        ];
    }
    public function getIsSupervisorAttribute()
    {
        if($this->role_id == User::PROVIDER_ROLE_ID)
        {
            return $this->provider->is_supervisor;
        }
        return false;
    }
    private function saveAvatarThumbnail($image, $directory, $name)
    {
        $width = getimagesize($image)[0];
        $height = getimagesize($image)[1];
        $newWidth = User::AVATAR_THUMBNAIL_WIDTH;
        $newHeight = $height * $newWidth / $width;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $mime =mime_content_type($image);
        $imageResource = null;
        switch ($mime) {
            case "image/jpeg":
                $imageResource = imagecreatefromjpeg($image);
                break;
            case "image/png":
                $imageResource = imagecreatefrompng($image);
                break;
        }
        imagecopyresized($newImage, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        switch ($mime) {
            case "image/jpeg":
                imagejpeg($newImage, $image);
                break;
            case "image/png":
                imagepng($newImage, $image);
                break;
        }

        Storage::putFileAs($directory, $image, User::AVATAR_THUMBNAIL_PREFIX . $name);
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

    public  const USER_STATS_VERIFIED_COUNT = 1;
    public  const USER_STATS_TOTAL_COUNT = 2;

    public const USER_ROLE_ID = 2;
    public const PROVIDER_ROLE_ID = 1;
    public const ADMIN_ROLE_ID = 3;

    public const AVATAR_THUMBNAIL_PREFIX = 'avatar_100_';
    public const  AVATAR_THUMBNAIL_WIDTH = 100;
}
