<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    protected $appends = ['status', 'ended_sessions_count', 'mean_score'];
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
    public function getStatusAttribute()
    {
        $status = Provider::PROVIDER_STATUS_OFFLINE;
        $openSessionsCount = $this->sessions()
            ->where('started', "!=", null)
            ->where('ended', null)->count();
        $activitySwitch = $this->activity_switch;
        if ($activitySwitch)
            $status = Provider::PROIDER_STATUS_ONLINE;
        if ($openSessionsCount > 0) {
            $status = Provider::PROVIDER_STATUS_IN_SESSION;
        }
        return $status;
    }
    public function getEndedSessionsCountAttribute()
    {

        $endedSessionsCount = $this->sessions()
            ->where('started', "!=", null)
            ->where('ended', "!=", null)->count();
        return $endedSessionsCount;
    }
    public function getMeanScoreAttribute()
    {
        $mean = 0;
        if ($this->ended_sessions_count > 0) {
            $mean = floatval($this->sessions()
                ->where('started', "!=", null)
                ->where('ended', "!=", null)
                ->where('score', "!=", null)
                ->avg('score'));
        }

        return $mean;
    }

    public  const PROVIDER_STATUS_OFFLINE = 0;
    public  const PROIDER_STATUS_ONLINE = 1;
    public const PROVIDER_STATUS_IN_SESSION = 2;
    public const PROVIDER_STATUS_NA = 3;
    public const EDUCATION_DEGREE_HIGH_SCHOOL_DIPLOMA = 1;
    public  const EDUCATION_DEGREE_BACHELORS = 2;
    public  const EDUCATION_DEGREE_MASTERS = 3;
    public  const EDUCATION_DEGREE_PHD = 4;

    public  const PROVIDER_STATS_ONLINE_COUNT = 1;
    public  const PROVIDER_STATS_IN_SESSION_COUNT = 2;
    public  const PROVIDER_STATS_TOTAL_COUNT = 3;
}
