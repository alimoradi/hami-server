<?php

namespace App\Libraries;

use App\Interfaces\VoiceCallMaker;
use App\User;
use GuzzleHttp\Client;

class KavenegarVoiceCall implements VoiceCallMaker
{
    var $apiToken = "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJoYW1pbGluZSIsInJvbGVzIjoidXNlciIsInVzZXJJZCI6OTAyODQxLCJhcHBsaWNhdGlvbklkIjoxMDg1LCJleHAiOjE2NTg3NDQ4NjR9.8Iv39xaqDZ4MkMgjX7jBrjl9oI0VHtCDFHNW1AWb5qk";
    public function createEndpoint($username)
    {
        $url = "https://api.kavenegar.io/voice/v1/callsn";

        $client = new Client(['headers' => [
            'Authorization' => 'Bearer ' . $this->apiToken
        ]]);

        $res = $client->request('POST', $url);

        return $res->getStatusCode() == 200;
    }
    public function createCall($callerUsername, $receptorUserName, $maxDuration)
    {

        $url = "https://api.kavenegar.io/voice/v1/calls";

        $client = new Client(['headers' => [
            'Authorization' => 'Bearer ' . $this->apiToken
        ]]);

        $res = $client->request('POST', $url, ['json' => ['caller' => [
            'username' => $callerUsername,
            'displayName' => 'anonymous'
        ], 'receptor' => [
            'username' => $receptorUserName,
            'displayName' => 'anonymous'
        ], 'maxDuration' => $maxDuration]]);
        if($res->getStatusCode() != 200)
            return false;

        return json_decode($res->getBody());

    }
}
