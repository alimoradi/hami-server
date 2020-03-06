<?php

namespace App\Libraries;
use App\Interfaces\AccountVerifier;
use App\User;
class SmsAccountVerification implements AccountVerifier
{
    public function generateVerificationCode()
    {
        return 1234;
        //return rand(1000,9999);
    }
    public function sendVerificationCode($verificationCode)
    {
        return true;
    }
}
