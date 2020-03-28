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
        if($size > 50000)
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
        $directory = 'message_files/user_'.auth()->user()->id;
        $name = uniqid().'.'.$extension;
        Storage::putFileAs($directory,$request->file('file'),$name);
        return response()->json(['name' => $name,
                'width' => $width,
                'height' => $height,
                'mime_type' => $mime,
                'size' => $size,
                'extension' => $extension
        ]);
        
    }
    public function downloadMessageFile($name)
    {
        $directory = 'message_files/user_'.auth()->user()->id. '/';
        return Storage::download($directory.$name);
    }
}
