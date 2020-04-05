<?php
namespace App\Interfaces;
use App\User;
 interface AccountVerifier
{
    /**
    @return string
    */
    public function generateVerificationCode();

    public function sendVerificationCode($verificationCode, $phoneNumber);
}
