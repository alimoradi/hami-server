<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    public static function createAffiliateDiscount($firstUserId, $secondUserId)
    {
         Discount::createAndSaveDiscountForUser($firstUserId,15000);
         Discount::createAndSaveDiscountForUser($secondUserId, 15000);
    }
    public static function createAndSaveDiscountForUser($userId, $value)
    {
        $discount = new Discount();
        $discount->value = $value;
        $discount->activated = true;
        $discount->user_id = $userId;
        $discount->code = Discount::generateDiscountCode();
        $discount->save();

    }
    public static function generateDiscountCode()
    {
        $random = rand(100000,999999);
        if(Discount::where('code', $random)->count() > 0)
        {
            return Discount::generateDiscountCode();
        }
        return $random;
         
    }

}
