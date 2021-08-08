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
    public static function createAndSaveDiscountForUser($userId, $value, $code = null)
    {
        $discount = new Discount();
        $discount->value = $value;
        $discount->activated = true;
        $discount->user_id = $userId;
        if($code == null)
        {
            $discount->code = Discount::generateDiscountCode();
        }
        else
        {
            $discount->code = $code;
        }
        $discount->save();

    }
    public static function redeemDiscount($userId, $code){
        $code = DiscountCode::where('code', $code)->first();
        if($code)
        {
            if(Discount::where('code', $code->code)->where('user_id', $userId)->count() > 0)
            {
                return false;
            }
            Discount::createAndSaveDiscountForUser($userId, $code->value, $code->code);
            return true;
        }
        return false;
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
