<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Invoice;
use App\Libraries\Notifications\SessionUpdated;
use App\Provider;
use App\Session;
use App\Topic;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Interfaces\VoiceCallMaker;
use App\SessionCall;

class SessionsController extends Controller
{
    public function request(Request $request)
    {
        $request->validate(
            [
                'reserved_from' => 'required',
                'reserved_to' => 'required',
                'provider_id' => 'required',
                'chat_topic_name' => 'required',
                'duration' => 'required',
                'type' => 'required',
                'timing_type' => 'required'
            ]
        );
        $providerId = $request->input('provider_id');
        $session = new Session();
        $session->request(auth()->user()->id,
            Carbon::parse($request->input('reserved_from')),
            Carbon::parse($request->input('reserved_to')),
            $providerId,
            $request->input('chat_topic_name'),
            $request->input('duration'),
            $request->input('type'),
            $request->input('timing_type'));

        //return response()->json($invoice);
        //$session->save();
        //$session->push();
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function accept($sessionId)
    {
        $session = Session::find($sessionId);
        $session->accepted = Carbon::now();
        $session->save();
        $session->subscribeToEachOther();
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function start($sessionId, VoiceCallMaker $callMaker)
    {
        $session = Session::find($sessionId);
        $session->started = Carbon::now();
        $session->save();
        if (Session::where('provider_id', $session->provider->id)
            ->where('user_id', $session->user->id)
            ->where('accepted', '!=', null)
            ->where('started', null)
            ->where('ended', null)->count() == 0
        ) {
            $session->unsubscribeFromEachOther();
        }
		if($session->type == Session::SESSION_TYPE_CALL && !SessionCall::getCall($sessionId))
		{
			SessionCall::createCall($session, $callMaker);
		}
		SessionCall::createCall($session, $callMaker);
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'sessionCall'])->find($session->id);
    }
    public function end($sessionId)
    {
        $session = Session::find($sessionId);
        $session->ended = Carbon::now();
        $session->save();

        if (Session::where('provider_id', $session->provider->id)
            ->where('user_id', $session->user->id)
            ->where('accepted', '!=', null)
            ->where('started', null)
            ->where('ended', null)->count() == 0
        ) {
            $session->unsubscribeFromEachOther();
        }
        if ($session->started == null) {
            $session->invoice->deleted = true;
            $session->invoice->save();
        }
        else
        {
            $session->invoice->amount = $session->finalCost() * -1;
            $session->invoice->is_final = true;
            $session->invoice->is_pre_invoice = false;

            $session->invoice->save();
        }
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
	public function hide($sessionId)
    {
        $session = Session::find($sessionId);
        $session->hidden = true;
        $session->save();
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])->find($session->id);
    }
    public function providerActiveSessions()
    {

        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'sessionCall'])
            ->whereHas('provider.user', function ($query) {
                $query->where('id', '=', auth()->user()->id);
            })
            ->where('started', '!=', null)
            ->where('ended', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function userActiveSessions()
    {
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])
            ->where('user_id', auth()->user()->id)
            ->where('started', '!=', null)
            ->where('ended', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function getSessions()
    {
		return auth()->user()->sessions;
        
    }
    public function getUserSessions($userId)
    {
        $user = User::find($userId);

        $sessions = null;
        if ($user->role_id == User::USER_ROLE_ID) {
            $sessions = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'DESC');
        } else if ($user->role_id == User::PROVIDER_ROLE_ID) {
            $sessions = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])
                ->whereHas('provider.user', function ($query) use ($user) {
                    $query->where('id', '=', $user->id);
                })
                ->orderBy('created_at', 'DESC');
        }
        return $sessions->get();
    }

    public function getPresentAndFutureSessions()
    {
        $sessions = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])
            ->where('ended', null)
            ->orderBy('started', 'DESC');
        if (auth()->user()->role_id == 2) {
            $sessions = $sessions->where('user_id', auth()->user()->id);
        } else if (auth()->user()->role_id == 1) {
            $sessions = $sessions->whereHas('provider.user', function ($query) {
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
        $sessions = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])
            ->where('ended', '!=', null)
            ->orderBy('started', 'DESC');
        if (auth()->user()->role_id == 2) {
            $sessions = $sessions->where('user_id', auth()->user()->id);
        } else if (auth()->user()->role_id == 1) {
            $sessions = $sessions->whereHas('provider.user', function ($query) {
                $query->where('id', '=', auth()->user()->id);
            });
        }
        return $sessions->get();
    }
    public function selectRangeByDate($userId, Request $request)
    {
        $request->validate([
            'from_date' => 'required', 'to_date' => 'required'
        ]);
        $user = User::find($userId);
        $fromDate = Carbon::parse($request->input('from_date'));
        $toDate = Carbon::parse($request->input('to_date'));
        $sessions = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])
            ->where('started', '>=', $fromDate)
            ->where('ended', '!=', null)
            ->where('started', '<=', $toDate);
        if ($user->role_id == User::USER_ROLE_ID) {
            $sessions = $sessions->where('user_id', $user->id);
        } else if ($user->role_id == User::PROVIDER_ROLE_ID) {
            $sessions = $sessions->whereHas('provider.user', function ($query) use ($user) {
                $query->where('id', '=', $user->id);
            });
        }
        return $sessions->get();
    }
    public function providerEndedSessions()
    {

        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])
            ->whereHas('provider.user', function ($query) {
                $query->where('id', '=', auth()->user()->id);
            })
            ->where('ended', '!=', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function userEndedSessions()
    {
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])
            ->where('user_id', auth()->user()->id)
            ->where('ended', '!=', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function getActiveRequests()
    {
        $role = auth()->user()->checkRole();
        $sessionQuery = "";
        if ($role == 'service_user') {
            $sessionQuery = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])
                ->where('user_id', auth()->user()->id);
        } else if ($role == 'service_provider') {
            $sessionQuery = Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories'])
                ->whereHas('provider', function ($query) {
                    $query->whereHas('user', function ($query) {
                        $query->where('id', '=', auth()->user()->id);
                    });
                });
        }
        return $sessionQuery
            ->where('started', null)
            ->where('ended', null)
            ->where('accepted', null)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
    public function checkRequestEligibility(Request $request)
    {

        $request->validate(
            [
                'provider_id' => 'required',
                'date_from' => 'required',
                'date_to' => 'required',
                'duration' => 'required',
                'type' => 'required',
                'timing_type' => 'required'
            ]
        );

        $timingType = $request->input('timing_type');
        $dateFrom = Carbon::parse($request->input('date_from'));
        $dateTo = Carbon::parse($request->input('date_to'));
        $duration = $request->input('duration');
        $type = $request->input('typer');
        $providerId = $request->input('provider_id');

        $provider = Provider::find($providerId);
        if (
            $timingType == Session::SESSION_TIMING_TYPE_IMMEDIATE
            && $provider->status != Provider::PROIDER_STATUS_ONLINE
        ) {
            return response()->json(['error' => 'provider not online', 'error_code' => 101], 409);
        }
        $confilictingProviderSessionsCount = Session::where('provider_id', $providerId)
            ->where('accepted', '!=', null)
            ->where('ended', null)
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($p) use ($dateFrom, $dateTo) {
                    $p->where('reserved_from', '>=', $dateFrom)->where('reserved_from', '<=', $dateTo);
                })->orWhere(function ($r) use ($dateFrom, $dateTo) {
                    $r->where('reserved_from', '<', $dateFrom)->where('reserved_to', '>', $dateFrom);
                });
            })->count();
        if ($confilictingProviderSessionsCount > 0) {
            return response()->json(['error' => 'provider sessions conflict', 'error_code' => 102], 409);
        }
        $conflictingUserSessionsCount = Session::where('user_id', auth()->user()->id)
            ->where(function ($j) use ($providerId) {
                $j->where('provider_id', '!=', $providerId)->orWhere('accepted', null);
            })->where('ended', null)
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($p) use ($dateFrom, $dateTo) {
                    $p->where('reserved_from', '>=', $dateFrom)->where('reserved_from', '<=', $dateTo);
                })->orWhere(function ($r) use ($dateFrom, $dateTo) {
                    $r->where('reserved_from', '<', $dateFrom)->where('reserved_to', '>', $dateFrom);
                });
            })->count();
        if ($conflictingUserSessionsCount > 0) {
            return response()->json(['error' => 'User sessions conflict', 'error_code' => 103], 409);
        }
        return response()->json(['success' => true]);
    }
    public function userRequestedSessions()
    {
        return Session::with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'sessionCall'])
            ->where('user_id', auth()->user()->id)
            ->where('started', null)
            ->orderBy('started', 'DESC')
            ->get();
    }
    public function notifySessionUpdate(Request $request)
    {

        $recipientUserId = $request->input('recipient_user_id');
        $sessionId = $request->input('session_id');
        $session = Session::find($sessionId);
        $user = User::where('id', $recipientUserId)->first();
        $user->notify(new SessionUpdated(json_encode($session), json_encode(auth()->user())));
        return response()->json(['success' => true]);
    }

    public function updateScore(Request $request, $sessionId)
    {
        $request->validate(
            [
                'score' => 'required'
            ]

        );
        $session = Session::find($sessionId);
        $session->score = $request->input('score');
        $session->save();
        return response()->json(['success' => true]);
    }
    public function getById($sessionId)
    {
        return Session::find($sessionId)->with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral', 'sessionCall'])->find($sessionId);
    }
    public function getSessionsState(Request $request)
    {

        $result = [];
        $sessions = auth()->user()->sessions()->select('sessions.id', 'sessions.started', 'sessions.accepted', 'sessions.ended', 'sessions.created_at')->get();

        foreach ($sessions as $key => $value) {
            array_push($result, ['id' => $value->id, 'state' => $value->state]);
        }

        return $result;
    }
}
