<?php

namespace App\Http\Controllers\Api\User\Payment;

use App\Models\Generalsetting;
use Illuminate\Http\Request;
use App\Models\Deposit;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Omnipay\Omnipay;

class PaymentController extends Controller
{
    public $_api_context;
    public $gateway;
    public function __construct()
    {
        $data = PaymentGateway::whereKeyword('paypal')->first();
        $paydata = $data->convertAutoData();

        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId($paydata['client_id']);
        $this->gateway->setSecret($paydata['client_secret']);
        $this->gateway->setTestMode(true);
    }

    public function store(Request $request)
    {

        if (!$request->has('deposit_number')) {
            return response()->json(['status' => false, 'data' => [], 'error' => 'Invalid Request']);
        }

        $deposit_number = $request->deposit_number;

        $deposit = Deposit::where('deposit_number', $deposit_number)->first();
        $curr = Currency::where('name', '=', $deposit->currency_code)->first();

        $support = ['USD', 'EUR'];
        if (!in_array($curr->name, $support)) {
            return redirect()->back()->with('unsuccess', 'Please Select USD Or EUR Currency For Paypal.');
        }

        $item_amount = $deposit->amount * $deposit->currency_value;


        $notify_url = action('Api\User\Payment\PaymentController@notify');
        $cancel_url = route('user.success', 0);
        try {
            $response = $this->gateway->purchase(array(
                'amount' => $item_amount,
                'currency' => $curr->name,
                'returnUrl' => $notify_url,
                'cancelUrl' => $cancel_url,
            ))->send();

            if ($response->isRedirect()) {
                Session::put('deposit_number', $deposit_number);
                if ($response->redirect()) {
                    return redirect($response->redirect());
                }
            } else {
                return redirect(route('user.success', 0));
            }
        } catch (\Throwable $th) {
            return redirect(route('user.success', 0));
        }
    }




    public function notify(Request $request)
    {

        $responseData = $request->all();
        $deposit_number = Session::get('deposit_number');
        if (empty($responseData['PayerID']) || empty($responseData['token'])) {
            return [
                'status' => false,
                'message' => __('Unknown error occurred'),
            ];
        }

        $transaction = $this->gateway->completePurchase(array(
            'payer_id' => $responseData['PayerID'],
            'transactionReference' => $responseData['paymentId'],
        ));

        $response = $transaction->send();

        if ($response->isSuccessful()) {

            $order = Deposit::where('deposit_number', $deposit_number)->first();
            $user = \App\Models\User::findOrFail($order->user_id);
            $user->balance = $user->balance + ($order->amount);
            $user->save();

            $order->method = "Paypal";
            $order->txnid = $response->getData()['transactions'][0]['related_resources'][0]['sale']['id'];
            $order->status = 1;
            $order->update();

            // store in transaction table
            if ($order->status == 1) {
                $transaction = new \App\Models\Transaction;
                $transaction->txn_number = Str::random(3) . substr(time(), 6, 8) . Str::random(3);
                $transaction->user_id = $order->user_id;
                $transaction->amount = $order->amount;
                $transaction->user_id = $order->user_id;
                $transaction->currency_sign = $order->currency;
                $transaction->currency_code = $order->currency_code;
                $transaction->currency_value = $order->currency_value;
                $transaction->method = $order->method;
                $transaction->txnid = $order->txnid;
                $transaction->details = 'Payment Deposit';
                $transaction->type = 'plus';
                $transaction->save();
            }

            return redirect(route('user.success', 1));
        } else {
            return redirect(route('user.success', 0));
        }
    }
}
