<?php

namespace App\Http\Controllers\Payment\Deposit;

use App\{
    Models\Deposit,
    Models\Transaction,
    Classes\GeniusMailer,
    Models\PaymentGateway,
};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class StripeController extends DepositBaseController
{

    public function __construct()
    {
        parent::__construct();
        $data = PaymentGateway::whereKeyword('stripe')->first();
        $paydata = $data->convertAutoData();
        Config::set('services.stripe.key', $paydata['key']);
        Config::set('services.stripe.secret', $paydata['secret']);
    }



    public function store(Request $request)
    {

        $data = PaymentGateway::whereKeyword('stripe')->first();
        $user = $this->user;

        $item_amount = $request->amount;
        $curr = $this->curr;

        $supported_currency = json_decode($data->currency_id, true);
        if (!in_array($curr->id, $supported_currency)) {
            return redirect()->back()->with('unsuccess', __('Invalid Currency For Stripe Payment.'));
        }


        try {
            $stripe_secret_key = Config::get('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                "success_url" => route('deposit.stripe.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                "cancel_url" => route('deposit.payment.cancle'),
                "customer_email" => $user->email,
                "locale" => "auto",
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => $this->curr->name,
                            "unit_amount" => $item_amount * 100,
                            "product_data" => [
                                "name" => $this->gs->title . ' Deposit'
                            ]
                        ]
                    ],
                ]
            ]);

            Session::put('input_data', $request->all());
            return redirect($checkout_session->url);
        } catch (Exception $e) {
            return back()->with('unsuccess', $e->getMessage());
        }
    }


    public function notify(Request $request)
    {
        $input = Session::get('input_data');

        $user = $this->user;
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);
        if ($response->status == 'complete') {

            $user->balance = $user->balance + ($input['amount'] / $this->curr->value);
            $user->mail_sent = 1;
            $user->save();

            $deposit = new Deposit;
            $deposit->user_id = $user->id;
            $deposit->currency = $this->curr->sign;
            $deposit->currency_code = $this->curr->name;
            $deposit->currency_value = $this->curr->value;
            $deposit->amount = $input['amount'] / $this->curr->value;
            $deposit->method = 'Stripe';
            $deposit->txnid = $response->payment_intent;
            $deposit->status = 1;
            $deposit->save();


            // store in transaction table
            if ($deposit->status == 1) {
                $transaction = new Transaction;
                $transaction->txn_number = Str::random(3) . substr(time(), 6, 8) . Str::random(3);
                $transaction->user_id = $deposit->user_id;
                $transaction->amount = $deposit->amount;
                $transaction->user_id = $deposit->user_id;
                $transaction->currency_sign  = $deposit->currency;
                $transaction->currency_code  = $deposit->currency_code;
                $transaction->currency_value = $deposit->currency_value;
                $transaction->method = $deposit->method;
                $transaction->txnid = $deposit->txnid;
                $transaction->details = 'Payment Deposit';
                $transaction->type = 'plus';
                $transaction->save();
            }

            $data = [
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
            $mailer->sendAutoMail($data);


            return redirect()->route('user-dashboard')->with('success', __('Balance has been added to your account.'));
        }
    }
}
