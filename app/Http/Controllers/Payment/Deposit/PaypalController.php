<?php

namespace App\Http\Controllers\Payment\Deposit;

use App\{
    Models\Deposit,
    Classes\GeniusMailer,
    Models\PaymentGateway
};

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Redirect;
use Session;
use Omnipay\Omnipay;

class PaypalController extends DepositBaseController
{
    public $_api_context;
    public $gateway;
    public function __construct()
    {
        parent::__construct();
        $data = PaymentGateway::whereKeyword('paypal')->first();
        $paydata = $data->convertAutoData();

        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId($paydata['client_id']);
        $this->gateway->setSecret($paydata['client_secret']);
        $this->gateway->setTestMode(true);
    }

    public function store(Request $request)
    {

        $data = PaymentGateway::whereKeyword('paypal')->first();
        $user = $this->user;

        $item_amount = $request->amount;
        $curr = $this->curr;

        $supported_currency = json_decode($data->currency_id, true);

        if (!in_array($curr->id, $supported_currency)) {
            return redirect()->back()->with('unsuccess', __('Invalid Currency For Paypal Payment.'));
        }

        $item_name = "Deposit via Paypal Payment";
        $cancel_url = route('deposit.payment.cancle');
        $notify_url = route('deposit.paypal.notify');

        $dep['user_id'] = $user->id;
        $dep['currency'] = $this->curr->sign;
        $dep['currency_code'] = $this->curr->name;
        $dep['amount'] = $request->amount / $this->curr->value;
        $dep['currency_value'] = $this->curr->value;
        $dep['method'] = 'Paypal';

        try {
            $response = $this->gateway->purchase(array(
                'amount' => $item_amount,
                'currency' => $curr->name,
                'returnUrl' => $notify_url,
                'cancelUrl' => $cancel_url,
            ))->send();

            if ($response->isRedirect()) {
                Session::put('input_data', $request->all());
                Session::put('deposit', $dep);

                if ($response->redirect()) {

                    return redirect($response->redirect());
                }
            } else {
                return redirect()->back()->with('unsuccess', $response->getMessage());
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('unsuccess', $th->getMessage());
        }
    }

    public function notify(Request $request)
    {
        $responseData = $request->all();
        $dep = Session::get('deposit');

        $success_url = route('deposit.payment.return');
        $cancel_url = route('deposit.payment.cancle');


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

            $deposit = new Deposit;
            $deposit->user_id = $dep['user_id'];
            $deposit->currency = $dep['currency'];
            $deposit->currency_code = $dep['currency_code'];
            $deposit->amount = $dep['amount'];
            $deposit->currency_value = $dep['currency_value'];
            $deposit->method = $dep['method'];
            $deposit->txnid = $response->getData()['transactions'][0]['related_resources'][0]['sale']['id'];
            $deposit->status = 1;
            $deposit->save();

            $user = \App\Models\User::findOrFail($deposit->user_id);
            $user->balance = $user->balance + ($deposit->amount);
            $user->save();

            // store in transaction table
            if ($deposit->status == 1) {
                $transaction = new \App\Models\Transaction;
                $transaction->txn_number = Str::random(3) . substr(time(), 6, 8) . Str::random(3);
                $transaction->user_id = $deposit->user_id;
                $transaction->amount = $deposit->amount;
                $transaction->user_id = $deposit->user_id;
                $transaction->currency_sign = $deposit->currency;
                $transaction->currency_code = $deposit->currency_code;
                $transaction->currency_value = $deposit->currency_value;
                $transaction->method = $deposit->method;
                $transaction->txnid = $deposit->txnid;
                $transaction->details = 'Payment Deposit';
                $transaction->type = 'plus';
                $transaction->save();
            }

            $maildata = [
                'to' => $user->email,
                'type' => "wallet_deposit",
                'cname' => $user->name,
                'damount' => $deposit->amount,
                'wbalance' => $user->balance,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'onumber' => "",
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($maildata);

            Session::forget('deposit');
            Session::forget('paypal_payment_id');
            return redirect($success_url);
        } else {
            return redirect($cancel_url);
        }
    }
}
