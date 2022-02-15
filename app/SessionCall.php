<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SessionCall extends Model
{
    protected $appends = ['duration'];
    public static function calculateMaxDuration($sessionId)
    {
        $sessionDuration = Session::find($sessionId)->duration * 60;
        $calls = SessionCall::where('session_id', $sessionId)->get();
        $pastDuration = 0;
        foreach ($calls as $call) {
            $pastDuration += $call->duration;
            //var_dump($call->id);
        }
        return $sessionDuration - $pastDuration;

    }
	public static function createCall($session, $callMaker) {
		$maxDuration = $session->duration;
        $call = $callMaker->createCall($session->user->phone, $session->provider->user->id, $maxDuration);
		if ($call) {
            $callId = strval(rand(10000000000,99999999999));
            $callerAccessToken = $call->output;
            $receptorAccessToken = $call->output;
            SessionCall::saveCall($callId
                , $session->user->id
                , $session->provider->user_id
                , $session->id
                , $callerAccessToken
                , $receptorAccessToken
                , $maxDuration);
            return true;
        }
		return false;
	}
	public static function getCall($sessionId) {
		return SessionCall::where('session_id', $sessionId)->first();
	}
    public function getDurationAttribute()
    {
        $beginDate = Carbon::parse($this->started_at);
        $endDate = Carbon::parse($this->ended_at);
        //var_dump($this->ended_at);
        return $endDate->diffInSeconds($beginDate);
    }
    public static function saveCall($id, $callerId, $receptorId, $sessionId, $callerToken, $receptorToken, $maxDuration)
    {
        $call = new SessionCall();
        $call->id = $id;
        $call->caller_id = $callerId;
        $call->receptor_id = $receptorId;
        $call->session_id = $sessionId;
        $call->caller_access_token = $callerToken;
        $call->receptor_access_token = $receptorToken;
        $call->max_duration = $maxDuration;
        $call->save();
    }
    public static function callStarted($id, $time)
    {
        $call = SessionCall::find($id);
        if($call->started == null)
        {
            $call->started_at = $time;
            $call->save();
        }
        return $call;
    }
    public static function callEnded($id, $time)
    {
        $call = SessionCall::find($id);
        if($call->ended_at == null)
        {
            $call->ended_at = $time;
            $call->save();
        }
        return $call;
    }

}
