<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Order;
use App\Models\PaymentGateway;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class StripeController extends Controller
{

    public function __construct()
    {

        $data = PaymentGateway::whereKeyword('stripe')->first();
        $paydata = $data->convertAutoData();
        Config::set('services.stripe.key', $paydata['key']);
        Config::set('services.stripe.secret', $paydata['secret']);
    }

    public function store(Request $request)
    {

        if ($request->has('order_number')) {
            $order_number = $request->order_number;
            $order = Order::where('order_number', $order_number)->firstOrFail();
            $curr = Currency::where('sign', '=', $order->currency_sign)->firstOrFail();
            if ($curr->name != "USD") {
                return redirect()->back()->with('unsuccess', 'Please Select USD Currency For Stripe.');
            }

            $item_amount = $order->pay_amount * $order->currency_value;
            $gs = Generalsetting::findOrFail(1);

            try {
                $stripe_secret_key = Config::get('services.stripe.secret');
                \Stripe\Stripe::setApiKey($stripe_secret_key);
                $checkout_session = \Stripe\Checkout\Session::create([
                    "mode" => "payment",
                    "success_url" => route('payment.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                    "cancel_url" => route('front.payment.cancle'),
                    "locale" => "auto",
                    "line_items" => [
                        [
                            "quantity" => 1,
                            "price_data" => [
                                "currency" => $order->currency_name,
                                "unit_amount" => $item_amount * 100,
                                "product_data" => [
                                    "name" => $gs->title . 'Payment'
                                ]
                            ]
                        ],
                    ]
                ]);

                Session::put('order_number', $order_number);
                return redirect($checkout_session->url);
            } catch (Exception $e) {
                return back()->with('unsuccess', $e->getMessage());
            }
        }
        return response()->json(['status' => false, 'data' => [], 'error' => 'Invalid Request']);
    }


    public function notify(Request $request)
    {
        $order_number = Session::get('order_number');
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);
        $order = Order::where('order_number', $order_number)->firstOrFail();

        if ($response->status == 'complete') {
            $order->method = "Stripe";
            $order->txnid = $response->payment_intent;
            $order->payment_status = 'Completed';
            $order->save();
            return redirect(route('front.payment.success', 1));
        } else {
            return redirect(route('front.payment.cancle'));
        }
    }
}
