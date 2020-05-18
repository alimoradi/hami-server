<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Provider;
use App\ProviderCategory;
use App\ProviderVerificationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProvidersController extends Controller
{
    public function getByCategoryId($categoryId)
    {
        
        return Provider::with(['user', 'providerCategory'])->where('provider_category_id', $categoryId)->get();
    }
    public function getByUserId($userId)
    {
        
        return Provider::with(['user', 'providerCategory', 'user.additionalInfo'])->where('user_id', $userId)->first();
    }
    public function getAll()
    {
        
        return Provider::with(['user', 'providerCategory'])->get();
    }
    public function getByUid($uid)
    {
        return Provider::with(['user'])->whereHas('user', function ($query) use($uid) {
            $query->where('tinode_uid', '=', $uid);
        })->first();
    }
    public function getById($id)
    {
        return Provider::with(['user', 'providerVerificationDocuments','user.additionalInfo'])->where('id', $id)->first();
    }
    public function verifyProvider($providerId)
    {
        $provider = Provider::find($providerId);
        $provider->verified_by_admin = true;
        $provider->save();
        return response()->json(['success' => true]);

    }
    public function downloadVerificationDocument(Request $request)
    {
        $request->validate(
            [
            'title'=> 'required'
            ]

        );
        $providerId = Provider::where('user_id',auth()->user()->id )->first()->id;

        $document = ProviderVerificationDocument::where("provider_id", $providerId)->where('title', $title = $request->input('title'))->first();
        if($document)
        {
            $directory = 'verification_documents/';
            //$image = Storage::url($directory.$document->url); 
            return response()->download(Storage::path($directory) .$document->url,null,
        [
            'Access-Control-Allow-Origin'=> '*',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
            'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
        ]
        );
        }
       
    }
    public function uploadVerificationDocument(Request $request)
    {
        $request->validate(
            [
            'file' => 'required',
            'title'=> 'required'
            ]

        );
        $mimeTypes = [
            "image/jpeg",
            "image/png",
            "application/pdf"
          ];
          $extensions=[
        ];
        $size = $request->file('file')->getsize();
        $title = $request->input('title');
        if($size > 5000000)
        {
            abort(413, "File too large");
        }
        $mime = $request->file('file')->getMimeType();
        $extension = $request->file('file')->extension();
        if(!in_array($mime, $mimeTypes) && !in_array($extension, $extensions))
        {
            abort(415, "Unsupported Media Type");
        }
        
        $directory = 'verification_documents';
        $name = uniqid("", true).'.'.$extension;
        Storage::putFileAs($directory,$request->file('file'),$name);
        $providerId = Provider::where('user_id',auth()->user()->id )->first()->id;
        $document = ProviderVerificationDocument::where("provider_id", $providerId)->where('title', $title)->delete();
       
        $document = new ProviderVerificationDocument();
        $document->provider_id = $providerId ;
        $document->url = $name;
        $document->title = $title;
        $document->save();
        return response()->json($document,200,[
        'Access-Control-Allow-Origin'=> '*',
        'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
        'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
    ]);
        
    }
    
}
