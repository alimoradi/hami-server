<?php

namespace App\Http\Controllers\Api;

use App\AdditionalInfo;
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
    public function updateInfo(Request $request)
    {
        $request->validate(
            [
            'national_code'=> 'required',
             'postal_code' => 'required',
             'land_line_number' => 'required',
            'address'=> 'required'
            ]

        );
        $info = null;
        $userId = auth()->user()->id;
        $info = AdditionalInfo::where('user_id',$userId )->first();
        if(!$info)
        {
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
        return AdditionalInfo::where('user_id',$userId )->first();

    }
}
