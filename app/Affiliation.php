<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    public static function createAffiliation($userId, $affiliateCode)
    {
        $affiliateId = User::where('phone', $affiliateCode)->first()->id;
        $affiliation = new Affiliation();
        $affiliation->visitor_id = $userId;
        $affiliation->affiliate_id = $affiliateId;
        $affiliation->confirmed = false;
        return $affiliation->save();
    }
    public static function confirmAffiliation($userId)
    {
        $affiliation = Affiliation::where('visitor_id', $userId)->first();
        if ($affiliation && $affiliation->confirmed == false) {
            $affiliation->confirmed = true;
            if ($affiliation->save()) {
                Discount::createAffiliateDiscount(
                    $affiliation->affiliate_id,
                    $affiliation->visitor_id
                );
            }
        }
    }
}
