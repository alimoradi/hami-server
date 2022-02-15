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
		return true;
        $apiKey = "79506870774B67524E73707245617652586B6B59314841465151426F626D616E4877503879567A76412F343D";
        $url = "https://api.kavenegar.com/v1/$apiKey/verify/lookup.json";
        $tokenString = "token=$verificationCode";
        $templateString="template=verify";
        $receptorString="receptor=$phoneNumber";
        $url = "$url?$tokenString&$templateString&$receptorString";
        $client = new Client();
        $res = $client->request('POST', $url);
        return $res->getStatusCode() == 200;
        return true;
    }
}
