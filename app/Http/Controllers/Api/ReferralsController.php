<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Provider;
use App\Session;
use App\SessionReferral;
use Illuminate\Http\Request;

class ReferralsController extends Controller
{
    public function referredToMe()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->firstOrFail();
        $providerCategoryIds = array_map(function ($cat) {
            return $cat['id'];
        }, $provider->providerCategories()->get()->toArray());
        $sessions = Session::whereHas('referral', function ($query) {
            $query->where('surveyed_at', null);
        })->whereHas('provider', function ($query) use ($providerCategoryIds) {
            $query->whereHas('providerCategories', function ($query) use ($providerCategoryIds) {
                $query->whereIn('provider_categories.id', $providerCategoryIds);
            });
        })->with(['provider', 'provider.user', 'user', 'provider.providerCategories', 'referral'])->get();
        return $sessions;
    }
    public function refer(Request $request, $sessionId)
    {
        $referNote = $request->input('refer_note');
        $session = Session::find($sessionId);
        $session->refer($referNote, auth()->user());

        return response()->json(['success' => true]);
    }
    public function reject($sessionId)
    {
        $referral = SessionReferral::where('session_id', $sessionId)->first();
        $referrer = Provider::where('user_id', auth()->user()->id)->first();
        return $referral->reject($referrer->id);
    }
    public function confirm($sessionId)
    {
        $referral = SessionReferral::where('session_id', $sessionId)->first();
        $referrer = Provider::where('user_id', auth()->user()->id)->first();
        return $referral->confirm($referrer->id);
    }
}
