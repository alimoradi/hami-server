<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function uploadMessageFile(Request $request)
    {
        
        $mimeTypes = [
            "image/jpeg",
            "image/gif",
            "image/png",
            "image/svg",
            "image/svg+xml"
          ];
        $size = $request->file('file')->getsize();
        if($size > 5000000)
        {
            abort(413, "File too large");
        }
        $mime = $request->file('file')->getMimeType();
        if(!in_array($mime, $mimeTypes))
        {
            abort(415, "Unsupported Media Type");
        }
        $extension = $request->file('file')->extension();
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
    public function downloadMessageFile($name)
    {
        $directory = 'message_files/';
        
        
        return response()->download(Storage::path($directory) .$name,null,
        [
            'Access-Control-Allow-Origin'=> '*',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin, Content-Type,Authorization',
            'Access-Control-Allow-Methods'=> 'GET, POST, PUT, DELETE, OPTIONS'
        ]
        );
    }
}
