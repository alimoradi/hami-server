<?php
namespace App\Interfaces;
 interface VoiceCallMaker
{
   
    public function createCall($callerPhoneNumber, $receptorPhoneNumber, $maxDuration);
    public function createEndpoint($username);
}
