<?php

namespace App\Libraries;

use App\Interfaces\VoiceCallMaker;
use App\User;
use GuzzleHttp\Client;

class BlueRoomVoiceCall implements VoiceCallMaker
{
    var $apiToken = "8bc61b9a06eb5cd60c0c48633de378bcfcac6218";
	var $url = "https://ws.blueroom.ir/api/v1/create/";
    public function createEndpoint($username)
    {
        
    }
    public function createCall($callerUsername, $receptorUserName, $maxDuration)
    {

        $url = $this->url.$this->apiToken.'/'.'10';

        $client = new Client();

        $res = $client->request('GET', $url);
        if($res->getStatusCode() != 200)
            return false;
			
        return json_decode($res->getBody());

    }
}
