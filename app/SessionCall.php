<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SessionCall extends Model
{
    public static function calculateMaxDuration($sessionId)
    {
        $sessionDuration = Session::find($sessionId)->duration * 60;
        $calls = SessionCall::where('session_id', $sessionId)->get();
        $pastDuration = 0;
        foreach ($calls as $call) {
            $pastDuration += $call->duration;
        }
        return $sessionDuration - $pastDuration;

    }
    public function getDurationAtribute()
    {
        $beginDate = Carbon::parse($this->started_at);
        $endDate = Carbon::parse($this->ended_at);
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
        $call->receptro_access_token = $receptorToken;
        $call->max_duration = $maxDuration;
        $call->save();
    }
}
