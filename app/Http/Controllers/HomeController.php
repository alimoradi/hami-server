<?php

namespace App\Http\Controllers;

use App\Payment;
use Exception;
use Illuminate\Http\Request;
use SoapClient;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function paymentCallback()
    {
        $success = false;
        $refId = "430434385";
        $MerchantID = 'abc437bf-29c0-4580-b4d7-618b4eff3a70';
        try{
            $Authority = $_GET['Authority'];
            $payment = Payment::getPaymentByAuthorityCode( $Authority);
            $Amount = $payment->amount;
            if ($_GET['Status'] == 'OK') {

                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => $MerchantID,
                        'Authority' => $Authority,
                        'Amount' => $Amount,
                    ]
                );

                if ($result->Status == 100) {
                    $payment->verify($result->RefID);
                    return view('payment-callback')->with(['success' => true, 'refId'=>$result->RefID]);
                } else {
                    return view('payment-callback')->with(['success' => false, 'refId'=> null]);
                }
            }
        }
        catch(Exception $ex)
        {
            
                    return view('payment-callback')->with(['success' => false, 'refId'=> null]);
        }
        
        
    }
}
