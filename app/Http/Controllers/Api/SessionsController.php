<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Invoice;
use App\Libraries\Notifications\SessionUpdated;
use App\Provider;
use App\Session;
use App\User;
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
             'chat_topic_name' => 'required',
             'duration' => 'required',
             'type' => 'required']
        );
        $providerId = $request->input('provider_id');
        $session = new Session();
        $session->user_id = auth()->user()->id;
        $session->provider_id =$providerId ;
        $session->chat_topic_name = $request->input('chat_topic_name');
        $session->reserved_from = Carbon::parse($request->input('reserved_from'));
        $session->reserved_to = Carbon::parse($request->input('reserved_to'));
        $session->type = $request->input('type');
        if($session->type == 1)
        {
            $session->per_minute_fee = Provider::find($providerId)->per_minute_text_fee;

        }
        else
        {
            $session->per_minute_fee = Provider::find($providerId)->per_minute_call_fee;

        }

        $session->duration = $request->input('duration');

        
        $invoice = new Invoice();
        $invoice->created_at = Carbon::now();
        $invoice->is_final = false;
        $invoice->is_pre_invoice = true;
        $invoice->amount = $session->per_minute_fee * $session->duration * -1;
        $invoice->user_id = auth()->user()->id;
        $invoice->related_type =  1;

        $session->save();
        $session->invoice()->save( $invoice);
        //return response()->json($invoice);
        //$session->save();
        //$session->push();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])->find($session->id);

    }
    public function accept($sessionId)
    {
        $session = Session::find($sessionId);
        $session->accepted = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function start($sessionId)
    {
        $session = Session::find($sessionId);
        $session->started = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function end($sessionId)
    {
        $session = Session::find($sessionId);
        $session->ended = Carbon::now();
        $session->save();
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function providerActiveSessions()
    {
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
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
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
            ->where('user_id' , auth()->user()->id)
            ->where('started', '!=' , null)
            ->where('ended', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function getUserSessions()
    {
        $sessions = Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
        ->where('user_id' , auth()->user()->id);
       
        return $sessions->get();
    }
    public function getPresentAndFutureSessions()
    {
        $sessions = Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
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
    public function getProviderPresentAndFutureSessions($providerId)
    {
        $sessions = Session::where('ended', null)
        ->orderBy('started', 'DESC')
        ->where('provider_id', $providerId);
        
        return $sessions->get();
    }
    public function getPastSessions()
    {
        $sessions = Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
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
    public function selectRangeByDate(Request $request)
    {
        $request->validate([
            'from_date' => 'required', 'to_date' => 'required'
        ]);
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $sessions = Session::where('started', '>=', $fromDate)
        ->where('ended', '!=' , null)
        ->where('started', '<=', $toDate);
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
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
        ->whereHas('provider.user' , function($query){
            $query->where('id', '=', auth()->user()->id);
        })
        ->where('ended', '!=' , null)
        ->orderBy('started', 'DESC')
        ->get();
    }
    public function userEndedSessions()
    {
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
            ->where('user_id' , auth()->user()->id)
            ->where('ended', '!=' , null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function providerRequestedSessions()
    {
        
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
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
        return Session::with(['provider','provider.user', 'user', 'provider.providerCategories'])
            ->where('user_id' , auth()->user()->id)
            ->where('started' , null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function notifySessionUpdate(Request $request)
    {
        
        $recipientUserId = $request->input('recipient_user_id');
        $sessionId = $request->input('session_id');
        $session = Session::find($sessionId);
        $user = User::where('id', $recipientUserId)->first();
        $user -> notify(new SessionUpdated(json_encode($session), json_encode(auth()->user())));
        return response()->json(['success'=> true]);
    }
}
