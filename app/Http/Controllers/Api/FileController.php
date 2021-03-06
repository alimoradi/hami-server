<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Image;

class FileController extends Controller
{
    public function uploadMessageFile(Request $request)
    {
        Log::info('called upload');
        $mimeTypes = [
            "image/jpeg",
            "image/gif",
            "image/png",
            "image/svg",
            "image/svg+xml",
            "auido/webm",
            "application/pdf"
          ];
        $extensions=[
            "webm"
        ];
        $size = $request->file('file')->getsize();
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
        $width = getimagesize($request->file('file'))[0];
        $height = getimagesize($request->file('file'))[1];
        $directory = 'message_files';
        $name = uniqid("", true).'.'.$extension;
        Storage::putFileAs($directory,$request->file('file'),$name);
        return response()->json(['name' => $name,
                'width' => $width,
                'height' => $height,
                'mime_type' => $mime,
                'size' => $size,
                'extension' => $extension
    ],200,[
        'Access-Control-Allow-Origin'=> '*',
        'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
        'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
    ]);

    }
    public function uploadAvatar(Request $request)
    {

        $mimeTypes = [
            "image/jpeg",
            "image/png"
          ];
          $extensions=[
        ];
        $image = $request->file('file');
        $size = $image->getsize();
        if($size > 2000000)
        {
            abort(413, "File too large");
        }
        $mime = $image->getMimeType();
        $extension = $image->extension();
        if(!in_array($mime, $mimeTypes) && !in_array($extension, $extensions))
        {
            abort(415, "Unsupported Media Type");
        }

       $result =  auth()->user()->saveAvatar($image);
        return response()->json($result,200,[
        'Access-Control-Allow-Origin'=> '*',
        'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
        'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
    ]);

    }
    public function downloadMessageFile($name)
    {
        Log::info('called download');
        $directory = 'message_files/';
        $image = Storage::url($directory.$name);



        return response()->download(Storage::path($directory) .$name,null,
        [
            'Access-Control-Allow-Origin'=> '*',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
            'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
        ]
        );
    }
}
