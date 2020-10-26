<?php

namespace App\Http\Controllers\Api;

use App\Fee;
use App\Http\Controllers\Controller;
use App\Provider;
use App\ProviderCategory;
use App\ProviderVerificationDocument;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProvidersController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        return ProviderCategory::find($categoryId)->providers()
        ->where('verified_by_admin', true)
        ->where('self_deactivated', false)
        ->with(['user', 'providerCategories', 'providerVerificationDocuments'])->get();
        return ProviderCategory::with(['user', 'providerCategories', 'providerVerificationDocuments'])->where('provider_category_id', $categoryId)->get();
    }
    public function getByUserId($userId)
    {

        return Provider::with(['user', 'providerCategories', 'providerVerificationDocuments', 'user.additionalInfo'])->where('user_id', $userId)->first();
    }
    public function getAll()
    {

        return Provider::with(['user', 'providerCategories'])->get();
    }
    public function getByUid($uid)
    {
        return Provider::with(['user'])->whereHas('user', function ($query) use ($uid) {
            $query->where('tinode_uid', '=', $uid);
        })->first();
    }
    public function getById($id)
    {
        return Provider::with(['user', 'providerCategories','providerVerificationDocuments', 'user.additionalInfo'])->where('id', $id)->first();
    }
    public function verifyProvider($providerId)
    {
        $provider = Provider::find($providerId);
        $provider->verified_by_admin = true;
        $provider->save();
        return response()->json(['success' => true]);
    }
    public function downloadVerificationDocument($name)
    {

        $directory = 'verification_documents/';
        //$image = Storage::url($directory.$document->url); 
        return response()->download(
            Storage::path($directory) . $name,
            null,
            [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
            ]
        );
    }
    public function uploadVerificationDocument(Request $request)
    {
        $request->validate(
            [
                'file' => 'required',
                'title' => 'required'
            ]

        );
        $mimeTypes = [
            "image/jpeg",
            "image/png",
            "application/pdf"
        ];
        $extensions = [];
        $size = $request->file('file')->getsize();
        $title = $request->input('title');
        if ($size > 5000000) {
            abort(413, "File too large");
        }
        $mime = $request->file('file')->getMimeType();
        $extension = $request->file('file')->extension();
        if (!in_array($mime, $mimeTypes) && !in_array($extension, $extensions)) {
            abort(415, "Unsupported Media Type");
        }

        $directory = 'verification_documents';
        $name = uniqid("", true) . '.' . $extension;
        Storage::putFileAs($directory, $request->file('file'), $name);
        $providerId = Provider::where('user_id', auth()->user()->id)->first()->id;
        $document = ProviderVerificationDocument::where("provider_id", $providerId)->where('title', $title)->delete();

        $document = new ProviderVerificationDocument();
        $document->provider_id = $providerId;
        $document->url = $name;
        $document->title = $title;
        $document->save();
        return response()->json($document, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
        ]);
    }
    public function updateProviderInfo(Request $request, $providerId)
    {
        $request->validate(
            [
                'provider_categories' => 'required',
                'per_minute_text_fee' => 'required',
                'per_minute_call_fee' => 'required',
                'education_degree' => 'required'
            ]

        );
        $provider = Provider::find($providerId);
        $provider->per_minute_text_fee = $request->input('per_minute_text_fee');
        $provider->per_minute_call_fee = $request->input('per_minute_call_fee');
        $provider->education_degree = $request->input('education_degree');
        $provider->providerCategories()->sync($request->input('provider_categories'));
        $provider->save();
        return Provider::with(['user', 'providerCategories', 'providerVerificationDocuments', 'user.additionalInfo'])->where('id', $providerId)->first();


    }
    public function updateAboutMe(Request $request, $providerId)
    {
        $request->validate(
            [
                'about_me' => 'required'
            ]

        );
        $provider = Provider::find($providerId);
        $provider->about_me = $request->input('about_me');
        $provider->save();
        return Provider::with(['user', 'providerCategories', 'providerVerificationDocuments', 'user.additionalInfo'])->where('id', $providerId)->first();


    }
    public function getFees()
    {
        return Fee::get();
    }
    public function activitySwitchOn()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->first();
        $provider->activity_switch = true;
        $provider->save();
        return response()->json(['success'=> true] );
    }
    public function providerStatsByStatus()
    {
        $onlineCount = Provider::where('activity_switch', true)->count();
        $totalCount = Provider::count();
        $inSessionCount =  Provider::whereHas('sessions', function ($query) {
            $query->where('started', '!=', null)
                ->where('ended', null);
        })->count();
        
        $stats = [
            Provider::PROVIDER_STATS_ONLINE_COUNT => $onlineCount,
            Provider::PROVIDER_STATS_TOTAL_COUNT => $totalCount,
            Provider::PROVIDER_STATS_IN_SESSION_COUNT => $inSessionCount
        ];
        return response()->json($stats);
    }
    public function activitySwitchOff()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->first();
        $provider->activity_switch = false;
        $provider->save();
        return response()->json(['success'=> true] );
    }
    public function selfDeactivate()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->first();
        $provider->self_deactivated = true;
        $provider->save();
        return response()->json(['success'=> true] );
    }
    public function selfActivate()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->first();
        $provider->self_deactivated = false;
        $provider->save();
        return response()->json(['success'=> true] );
    }
    public function getActivitySwitch()
    {
        $provider = Provider::where('user_id', auth()->user()->id)->first();
        
        return response()->json($provider->activity_switch );
    }
    public function getRandomAvatars($categoryId = null, Request $request)
    {
        $avatars = [];
        $query =  $query = User::whereHas('provider')->where('avatar', '!=', null);
        if($categoryId)
        {
            $query = $query->whereHas('provider.providerCategories', function ($query) use($categoryId) {
                $query->where('provider_categories.id',$categoryId);
            });
        }
        $query = $query->inRandomOrder()->limit(7);
        $thumbnailArray = Array();
        foreach($query->get() as $user)
        {
            $thumbnailArray[] = $user->avatar_thumbnail;
        }
        return response()->json($thumbnailArray );
       
    }
    public function makeSupervisor($providerId)
    {
        $provider = Provider::find($providerId);
        $provider->is_supervisor = true;
        $provider->save();
        return response()->json(['success'=> true] );
    }
    public function unmakeSupervisor($providerId)
    {
        $provider = Provider::find($providerId);
        $provider->is_supervisor = false;
        $provider->save();
        return response()->json(['success'=> true] );
    }
}
