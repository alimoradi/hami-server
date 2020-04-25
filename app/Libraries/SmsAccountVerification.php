<?php

namespace App\Libraries;
use App\Interfaces\AccountVerifier;
use App\User;
use GuzzleHttp\Client;
class SmsAccountVerification implements AccountVerifier
{
    public function generateVerificationCode()
    {
        //return 1234;
        return rand(1000,9999);
    }
    public function sendVerificationCode($verificationCode, $phoneNumber)
    {
        $apiKey = "4A7674346D57477642546549686457495334474735526F45573774714A596C3444745A744B564D59496E553D";
        $url = "https://api.kavenegar.com/v1/$apiKey/verify/lookup.json";
        $tokenString = "token=$verificationCode";
        $templateString="template=hami";
        $receptorString="receptor=$phoneNumber";
        $url = "$url?$tokenString&$templateString&$receptorString";
        $client = new Client();
        $res = $client->request('POST', $url);
        return $res->getStatusCode() == 200;
        return true;
    }
}
