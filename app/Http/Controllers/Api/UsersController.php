<?php

namespace App\Http\Controllers\Api;

use App\AdditionalInfo;
use App\Libraries\Notifications\MessageReceived;
use App\Http\Controllers\Controller;
use App\Invoice;
use App\Provider;
use App\Session;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
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
        $user->notify(new MessageReceived(json_encode(auth()->user()), $topic));
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
        $spendable = $query->where(function ($q) {
            $q->where('amount', '>', 0)->where('is_final', true);
        })->orWhere('amount', '<', 0);
        $real = $spendable->where('is_final', true)->sum('amount');
        $spendable = $spendable->sum('amount');

        return response()->json(['real' => $real, 'spendable'=>$spendable ]);
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
}
