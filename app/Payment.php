<?php

namespace App;

use App\Libraries\Notifications\PaymentConfirmed;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function createInvoice()
    {
        $invoice = new Invoice();
        $invoice->created_at = Carbon::now();
        $invoice->is_final = false;
        $invoice->related_type = 2;
        $invoice->is_pre_invoice = true;
        $invoice->amount = $this->amount;
        $invoice->user_id = $this->user_id;
        $invoice->related_id = $this->id;
        $invoice->save();
        return $invoice;
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'related_id')->where('related_type', 2);
    }
    public static function getPaymentByAuthorityCode($authorityCode)
    {
        return Payment::where('authority_code',$authorityCode )
            ->with(['user', 'invoice'])->first();
    }
    public function verify($referenceId, $skipTempDiscount = false)
    {
        $this->is_verified = true;
        $this->reference_id = $referenceId;
        $this->invoice->is_final = true;
        $this->invoice->is_pre_invoice = false;

        $this->save();
        $this->invoice->save();
        //temp double discount
        if(!$skipTempDiscount)
        {
            $this->user->deposit($this->amount, true);
        }

        try{
            $this->user->notify(new PaymentConfirmed(strval($this->reference_id)));
        }
        catch(Exception $ex)
        {

        }


    }

}
