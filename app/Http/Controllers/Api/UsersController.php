<?php

namespace App\Http\Controllers\Api;

use App\AdditionalInfo;
use App\Libraries\Notifications\MessageReceived;
use App\Http\Controllers\Controller;
use App\Interfaces\VoiceCallMaker;
use App\Invoice;
use App\Libraries\Notifications\IncomingCall;
use App\Payment;
use App\Provider;
use App\ProviderCategory;
use App\Session;
use App\Subscription;
use App\Topic;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SoapClient;

class UsersController extends Controller
{
    public function me()
    {
        return User::with(['sessionSubscriptions', 'p2pSubscriptions', 'mustSubscriptions'])->find(auth()->user()->id);
    }
    public function getById($id)
    {


        return User::where('id', $id)->first();
    }
    public function getByUid($uid)
    {
        return User::where('tinode_uid', $uid)->first();
    }

    public function notifySentMessage(Request $request)
    {

        $recipientUserId = $request->input('recipient_user_id');
        $topic = $request->input('topic');
        $user = User::where('id', $recipientUserId)->first();
        return $user->notify(new MessageReceived(json_encode(auth()->user()), $topic));
        return response()->json(['success' => true]);
    }
    public function updateInfo(Request $request)
    {

        $info = null;
        $userId = auth()->user()->id;
        $info = AdditionalInfo::where('user_id', $userId)->first();
        if (!$info) {
            $info = new AdditionalInfo();
            $info->user_id = $userId;
        }

        $info->address = $request->input('address');
        $info->national_code = $request->input('national_code');
        $info->land_line_number = $request->input('land_line_number');
        $info->postal_code = $request->input('postal_code');
        $info->save();

        return $info;
    }
    public function getAdditionalInfo($userId)
    {
        return AdditionalInfo::where('user_id', $userId)->first();
    }
    public function getBalance()
    {
        $userId = auth()->user()->id;
        $query = Invoice::where('user_id', $userId);
        $spendable = $query->where(function ($j) {
            $j->where(function ($q) {
                $q->where('amount', '>', 0)->where('is_final', true);
            })->orWhere('amount', '<', 0);
        })->where('deleted', false);


        $spendableAmount = $spendable->sum('amount');
        $real = $spendable->where('is_final', true)->sum('amount');
        return response()->json(['real' => $real, 'spendable' => $spendableAmount]);
    }
    public function deposit(Request $request)
    {
        $amount = $request->input('amount');
        $invoice = auth()->user()->deposit($amount);
        return response()->json($invoice);
    }
    public function tempInvoiceCreate()
    {
        return;
        $sessions = Session::get();
        foreach ($sessions as $session) {
            $provider = Provider::where('id', $session->provider_id)->first();
            $providersUserId = $provider->user_id;

            for ($i = 0; $i < 2; $i++) {
                $invoice = new Invoice();
                $invoice->created_at = $session->created_at;
                $amount = 0;
                if ($session->started != null) {
                    $invoice->is_pre_invoice = false;
                    $endDate = Carbon::now();
                    if ($session->ended) {
                        $endDate = Carbon::parse($session->ended);
                        $invoice->is_final = true;
                    }
                    $beginDate = Carbon::parse($session->started);
                    $duration = $endDate->diffInMinutes($beginDate);
                    if ($duration < 5) {
                        $amount = 0;
                    } else {
                        $amount = $duration * $session->per_minute_text_fee;
                    }
                } else {
                    $amount = 60 * $session->per_minute_text_fee;

                    $invoice->is_pre_invoice = true;
                }
                if ($i == 0) {
                    $invoice->amount = (-1) * $amount;
                    $invoice->user_id = $session->user_id;
                } else if ($i == 1) {
                    $invoice->amount = $amount;
                    $invoice->user_id = $providersUserId;
                }
                $invoice->save();
            }
        }
    }
    public function config()
    {
        $providerPresTopic = Topic::where('type', 3)->first()->name;

        $config = [
            'provider_pres_topic' => $providerPresTopic
        ];
        return response()->json($config);
    }
    public function usersStats()
    {
        $totalCount = User::count();
        $verified =  User::where('phone_verified_at', '!=', null)->count();

        $stats = [
            User::USER_STATS_TOTAL_COUNT => $totalCount,
            User::USER_STATS_VERIFIED_COUNT => $verified
        ];
        return response()->json($stats);
    }
    public function stats()
    {
        $onlineProviderCount = Provider::where('activity_switch', true)->count();
        $totalProviderCount = Provider::count();
        $inSessionProviderCount =  Provider::whereHas('sessions', function ($query) {
            $query->where('started', '!=', null)
                ->where('ended', null);
        })->count();
        $cats = ProviderCategory::all();
        $categoryProviderStats = [];
        foreach ($cats as $cat) {
            $catId = $cat->id;
            $onlineCatProviderCount = Provider::where('activity_switch', true)
                ->whereHas('providerCategories', function ($query) use ($catId) {
                    $query->where('provider_categories.id', $catId);
                })->count();
            $totalCatProviderCount = Provider::whereHas('providerCategories', function ($query) use ($catId) {
                $query->where('provider_categories.id', $catId);
            })->count();
            $inSessionCatProviderCount =  Provider::whereHas('sessions', function ($query) {
                $query->where('started', '!=', null)
                    ->where('ended', null);
            })->whereHas('providerCategories', function ($query) use ($catId) {
                $query->where('provider_categories.id', $catId);
            })->count();
            $categoryProviderStats[] = [
                'category_id' => $catId,
                'online_provider_count' => $onlineCatProviderCount,
                'total_provider_count' => $totalCatProviderCount,
                'in_session_provider_count' => $inSessionCatProviderCount
            ];
        }
        $stats = [
            'online_provider_count' => $onlineProviderCount,
            'total_provider_count' => $totalProviderCount,
            'in_session_provider_count' => $inSessionProviderCount,
            'category_stats' => $categoryProviderStats
        ];
        return response()->json($stats);
    }

    public function makeCall(Request $request, VoiceCallMaker $callMaker)
    {


        $request->validate(
            [
                'receptor_user_id' => 'required',
                'max_duration' => 'required'
            ]
        );
        $callerUsername = auth()->user()->phone;
        $receptor = User::find($request->input('receptor_user_id'));
        $receptorUsername = $receptor->phone;
        $maxDuration = $request->input('max_duration');
        $call = $callMaker->createCall($callerUsername, $receptorUsername, $maxDuration);


        if ($call) {
            $callId = $call->id;
            $callerAccessToken = $call->caller->accessToken;
            $receptorAccessToken = $call->receptor->accessToken;;
            $receptor->notify(new IncomingCall($receptorAccessToken, $callId, json_encode(auth()->user()), strval($maxDuration)));
            return response()->json(['id' => $callId, 'access_token' => $callerAccessToken]);
        }
        return response()->json('', 404);
    }
    public function getPeers()
    {
        return auth()->user()->p2pPeers();
    }
    public function getDiscounts()
    {
        return auth()->user()->discounts()->where('activated', true)->get();
    }
    public function useDiscount(Request $request, $discountId)
    {

        $discount = auth()->user()->discounts()
            ->where('id', $discountId)
            ->where('activated', true)
            ->where('expired', false)->first();
        if ($discount) {
            $invoice = auth()->user()->deposit($discount->value);
            $discount->expired = true;
            $discount->expired_at = Carbon::now();
            $discount->save();
            return response()->json($invoice);
        }
        return response()->json('', 404);
    }
    public function getAll()
    {
        return User::where('role_id', User::USER_ROLE_ID)->get();
    }
    public function getPaymentAuthority($amount)
    {
        $MerchantID = 'abc437bf-29c0-4580-b4d7-618b4eff3a70'; //Required
        $Amount = $amount;
        $Description = 'افزایش اعتبار'; // Required
        $Mobile = auth()->user()->phone;
        $CallbackURL = 'http://51.210.61.202/api/payment/paymentCallback'; // Required
        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest(
            [
                'MerchantID' => $MerchantID,
                'Amount' => $Amount,
                'Description' => $Description,
                'Mobile' => $Mobile,
                'CallbackURL' => $CallbackURL,
            ]
        );
        if ($result->Status == 100) {
            auth()->user()->requestDeposit($Amount,$result->Authority);
            return response()->json(['authority_code' => $result->Authority]);
        }
        return response()->json(['error' => 'payment authority failed', 'error_code' => 109], 400);
    }
    public function paymentCallback()
    {
        $MerchantID = 'abc437bf-29c0-4580-b4d7-618b4eff3a70';
        
        $Authority = $_GET['Authority'];
        $payment = Payment::getPaymentByAuthorityCode( $Authority);
        $Amount = $payment->amount;
        if ($_GET['Status'] == 'OK') {

            $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

            $result = $client->PaymentVerification(
                [
                    'MerchantID' => $MerchantID,
                    'Authority' => $Authority,
                    'Amount' => $Amount,
                ]
            );

            if ($result->Status == 100) {
                $payment->verify($result->RefID);
                echo 'Transaction success. RefID:' . $result->RefID;
            } else {
                echo 'Transaction failed. Status:' . $result->Status;
            }
        } else {
            echo 'Transaction canceled by user';
        }
    }
    public function payments()
    {
        return auth()->user()->payments;
    }
}
