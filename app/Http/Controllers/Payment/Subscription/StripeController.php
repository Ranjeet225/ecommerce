<?php

namespace App\Http\Controllers\Payment\Subscription;

use App\{
    Models\Subscription,
    Classes\GeniusMailer,
    Models\PaymentGateway,
    Models\UserSubscription
};
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class StripeController extends SubscriptionBaseController
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

        $this->validate($request, [
            'shop_name'   => 'unique:users',
        ], [
            'shop_name.unique' => __('This shop name has already been taken.')
        ]);

        $subs = Subscription::findOrFail($request->subs_id);
        $data = PaymentGateway::whereKeyword('stripe')->first();
        $user = $this->user;

        $item_amount = $subs->price * $this->curr->value;
        $curr = $this->curr;

        $supported_currency = json_decode($data->currency_id, true);
        if (!in_array($curr->id, $supported_currency)) {
            return redirect()->back()->with('unsuccess', __('Invalid Currency For Stripe Payment.'));
        }


        $sub['user_id'] = $user->id;
        $sub['subscription_id'] = $subs->id;
        $sub['title'] = $subs->title;
        $sub['currency_sign'] = $this->curr->sign;
        $sub['currency_code'] = $this->curr->name;
        $sub['currency_value'] = $this->curr->value;
        $sub['price'] = $subs->price * $this->curr->value;
        $sub['price'] = $sub['price'] / $this->curr->value;
        $sub['days'] = $subs->days;
        $sub['allowed_products'] = $subs->allowed_products;
        $sub['details'] = $subs->details;
        $sub['method'] = 'Stripe';


        try {
            $stripe_secret_key = Config::get('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                "success_url" => route('user.stripe.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                "cancel_url" => route('user.payment.cancle'),
                "customer_email" => $user->email,
                "locale" => "auto",
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => $this->curr->name,
                            "unit_amount" => $item_amount * 100,
                            "product_data" => [
                                "name" => $this->gs->title . ' ' . $subs->title . ' Plan',
                            ]
                        ]
                    ],
                ]
            ]);

            Session::put('subscription_data', $sub);
            return redirect($checkout_session->url);
        } catch (Exception $e) {
            return back()->with('unsuccess', $e->getMessage());
        }
    }


    public function notify(Request $request)
    {

        $subdata = Session::get('subscription_data');
        $user = $this->user;
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);

        if ($response->status == 'complete') {

            $order = new UserSubscription;
            $order->user_id = $subdata['user_id'];
            $order->subscription_id = $subdata['subscription_id'];
            $order->title = $subdata['title'];
            $order->currency_sign = $this->curr->sign;
            $order->currency_code = $this->curr->name;
            $order->currency_value = $this->curr->value;
            $order->price = $subdata['price'];
            $order->days = $subdata['days'];
            $order->allowed_products = $subdata['allowed_products'];
            $order->details = $subdata['details'];
            $order->method = $subdata['method'];
            $order->txnid = $response->payment_intent;
            $order->status = 1;

            $user = User::findOrFail($order->user_id);
            $package = $user->subscribes()->where('status', 1)->orderBy('id', 'desc')->first();
            $subs = Subscription::findOrFail($order->subscription_id);

            $today = Carbon::now()->format('Y-m-d');
            $user->is_vendor = 2;
            if (!empty($package)) {
                if ($package->subscription_id == $order->subscription_id) {
                    $newday = strtotime($today);
                    $lastday = strtotime($user->date);
                    $secs = $lastday - $newday;
                    $days = $secs / 86400;
                    $total = $days + $subs->days;
                    $input['date'] = date('Y-m-d', strtotime($today . ' + ' . $total . ' days'));
                } else {
                    $input['date'] = date('Y-m-d', strtotime($today . ' + ' . $subs->days . ' days'));
                }
            } else {
                $input['date'] = date('Y-m-d', strtotime($today . ' + ' . $subs->days . ' days'));
            }

            $input['mail_sent'] = 1;
            $user->update($input);
            $order->save();

            $maildata = [
                'to' => $user->email,
                'type' => "vendor_accept",
                'cname' => $user->name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'onumber' => "",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendAutoMail($maildata);
            Session::forget('subscription_data');
            return redirect()->route('user-dashboard')->with('success', __('Subscription Activated Successfully'));
        }
    }
}
