<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Provider;
use App\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function request($providerId, $chatTopicName)
    {
        $session = new Session();
        $session->user_id = auth()->user()->id;
        $session->provider_id = $providerId;
        $session->chat_topic_name = $chatTopicName;
        
        $session->per_minute_text_fee = Provider::find($providerId)->per_minute_text_fee;
        $session->save();

        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])->find($session->id);

    }
    public function start($sessionId)
    {
        $session = Session::find($sessionId);
        $session->accepted = true;
        $session->started = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])->find($session->id);
    }
    public function end($sessionId)
    {
        $session = Session::find($sessionId);
        $session->ended = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])->find($session->id);
    }
    public function providerActiveSessions()
    {

    }
    public function userActiveSessions()
    {
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
            ->where('user_id' , auth()->user()->id)
            ->where('started', '!=' , null)
            ->where('ended', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function userEndedSessions()
    {
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
            ->where('user_id' , auth()->user()->id)
            ->where('ended', '!=' , null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function userRequestedSessions()
    {
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
            ->where('user_id' , auth()->user()->id)
            ->where('started' , null)
            ->orderBy('started', 'DESC')
            ->get();
    }
}
