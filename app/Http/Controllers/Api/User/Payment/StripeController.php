<?php

namespace App\Http\Controllers\Api\User\Payment;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Generalsetting;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class StripeController extends Controller
{

    public function __construct()
    {

        $data = PaymentGateway::whereKeyword('stripe')->first();
        $paydata = $data->convertAutoData();
        \Config::set('services.stripe.key', $paydata['key']);
        \Config::set('services.stripe.secret', $paydata['secret']);
    }

    public function store(Request $request)
    {

        $deposit = Deposit::where('deposit_number', $request->deposit_number)->first();
        $item_amount = $deposit->amount * $deposit->currency_value;
        $curr = Currency::where('name', '=', $deposit->currency_code)->first();
        $gs = Generalsetting::findOrFail(1);

        try {
            $stripe_secret_key = Config::get('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                "success_url" => route('api.user.deposit.stripe.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                "cancel_url" => route('front.payment.cancle'),
                "locale" => "auto",
                
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => $curr->name,
                            "unit_amount" => $item_amount * 100,
                            "product_data" => [
                                "name" => $gs->title . 'Deposit'
                            ]
                        ]
                    ],
                ]
            ]);

            Session::put('deposit_id', $request->deposit_number);
            return redirect($checkout_session->url);
        } catch (Exception $e) {
            return back()->with('unsuccess', $e->getMessage());
        }
    }


    public function notify(Request $request)
    {
       
        $deposit_number = Session::get('deposit_id');
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);
        $deposit = Deposit::where('deposit_number', $deposit_number)->firstOrFail();



        if ($response->status == 'complete') {
            $user = \App\Models\User::findOrFail($deposit->user_id);
            $user->balance = $user->balance + ($deposit->amount);
            $user->save();
            $deposit['status'] = 1;
            $deposit['method'] = 'Stripe';
            $deposit['txnid'] = $response->payment_intent;
            $deposit->update();
            // store in transaction table
            if ($deposit->status == 1) {
                $transaction = new Transaction;
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
            return redirect(route('user.success', 1));
        } else {
            return redirect(route('user.success', 0));
        }
    }
}
