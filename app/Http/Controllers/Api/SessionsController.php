<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Provider;
use App\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function request(Request $request)
    {
        $request->validate(
            ['reserved_from'=> 'required',
             'reserved_to' => 'required',
             'provider_id' => 'required',
             'chat_topic_name' => 'required']
        );
        $providerId = $request->input('provider_id');
        $session = new Session();
        $session->user_id = auth()->user()->id;
        $session->provider_id =$providerId ;
        $session->chat_topic_name = $request->input('chat_topic_name');
        $session->reserved_from = Carbon::parse($request->input('reserved_from'));
        $session->reserved_to = Carbon::parse($request->input('reserved_to'));
        $session->per_minute_text_fee = Provider::find($providerId)->per_minute_text_fee;
        $session->save();

        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])->find($session->id);

    }
    public function accept($sessionId)
    {
        $session = Session::find($sessionId);
        $session->accepted = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])->find($session->id);
    }
    public function start($sessionId)
    {
        $session = Session::find($sessionId);
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
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
        ->whereHas('provider.user' , function($query){
                $query->where('id', '=', auth()->user()->id);
        })
        ->where('started', '!=' , null)
        ->where('ended', null)
        ->orderBy('started', 'DESC')
        ->get();
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
    public function getPresentAndFutureSessions()
    {
        $sessions = Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
        ->where('ended', null)
        ->orderBy('started', 'DESC');
        if(auth()->user()->role_id == 2)
        {
            $sessions = $sessions->where('user_id' , auth()->user()->id);
        }
        else if(auth()->user()->role_id == 1)
        {
            $sessions = $sessions->whereHas('provider.user' , function($query){
                $query->where('id', '=', auth()->user()->id);
            });
        }
        return $sessions->get();
    }
    public function getPastSessions()
    {
        $sessions = Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
        ->where('ended','!=', null)
        ->orderBy('started', 'DESC');
        if(auth()->user()->role_id == 2)
        {
            $sessions = $sessions->where('user_id' , auth()->user()->id);
        }
        else if(auth()->user()->role_id == 1)
        {
            $sessions = $sessions->whereHas('provider.user' , function($query){
                $query->where('id', '=', auth()->user()->id);
            });
        }
        return $sessions->get();
    }
    public function providerEndedSessions()
    {
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
        ->whereHas('provider.user' , function($query){
            $query->where('id', '=', auth()->user()->id);
        })
        ->where('ended', '!=' , null)
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
    public function providerRequestedSessions()
    {
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategory'])
        ->whereHas('provider' , function($query){
            $query->whereHas('user', function($query){
                $query->where('id', '=', auth()->user()->id);
            });
               
        })
        ->where('started' , null)
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
