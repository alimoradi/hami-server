<?php

namespace App\Http\Controllers\Api;

use App\Libraries\Notifications\MessageReceived;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

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
        $user -> notify(new MessageReceived( json_encode(auth()->user()), $topic));
        return response()->json(['success'=> true]);
    }
}
