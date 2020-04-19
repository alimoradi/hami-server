<?php

namespace App\Http\Controllers\Api;

use App\AvailableHours;
use App\Http\Controllers\Controller;
use App\Provider;
use Illuminate\Http\Request;

class AvailableHoursController extends Controller
{
    public function add(Request $request)
    {
        $request->validate(['time_from'=> 'required', 'time_to' => 'required', 'repeating_day_of_week'=>'required']);

        $hours = new AvailableHours();
        $hours->provider_id = Provider::where('user_id',auth()->user()->id )->first()->id;
        $hours->time_from = $request->input('time_from');
        $hours->time_to = $request->input('time_to');
        $hours->repeating_day_of_week = $request->input('repeating_day_of_week');
        $hours->save();

        return AvailableHours::with(['provider'])->find($hours->id);
    }
    public function remove($availableHourId)
    {
        $hours = AvailableHours::find($availableHourId);
        $hours->expired = true;
        $hours->save();
        return  $hours;
    }
    public function disable($availableHourId)
    {
        $hours = AvailableHours::find($availableHourId);
        $hours->disabled = true;
        $hours->save();
        return response()->json(['success' => true]);
    }
    public function enable($availableHourId)
    {
        $hours = AvailableHours::find($availableHourId);
        $hours->disabled = false;
        $hours->save();
        return response()->json(['success' => true]);
    }
    public function toggleDisabled($availableHourId)
    {
        $hours = AvailableHours::find($availableHourId);
        $hours->disabled = !$hours->disabled;
        $hours->save();
        return $hours;
    }
    public function get(Request $request)
    {
        $providerId = $request->input('provider_id');
        if(!$providerId)
        {
            $providerId = Provider::where('user_id',auth()->user()->id )->first()->id;
        }
       
        return AvailableHours::where('provider_id', $providerId)->where("expired", false)->get();
    }
}
