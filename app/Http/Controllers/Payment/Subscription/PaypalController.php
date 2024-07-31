<?php

namespace App\Http\Controllers\Payment\Subscription;

use App\{
    Models\User,
    Models\Subscription,
    Classes\GeniusMailer,
    Models\PaymentGateway,
    Models\UserSubscription
};

use Illuminate\{
    Http\Request,
    Support\Facades\Session
};
use Omnipay\Omnipay;


use Illuminate\Support\Str;
use Carbon\Carbon;

class PaypalController extends SubscriptionBaseController
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

        $this->validate($request, [
            'shop_name'   => 'unique:users',
        ], [
            'shop_name.unique' => __('This shop name has already been taken.')
        ]);

        $subs = Subscription::findOrFail($request->subs_id);
        $data = PaymentGateway::whereKeyword('paypal')->first();
        $user = $this->user;

        $item_amount = $subs->price * $this->curr->value;
        $curr = $this->curr;

        $supported_currency = json_decode($data->currency_id, true);
        if (!in_array($curr->id, $supported_currency)) {
            return redirect()->back()->with('unsuccess', __('Invalid Currency For Paypal Payment.'));
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
        $sub['method'] = 'Paypal';

        $order['item_name'] = $subs->title . " Plan";
        $order['item_number'] = Str::random(4) . time();
        $order['item_amount'] = $item_amount;
        $cancel_url = route('user.payment.cancle');
        $notify_url = route('user.paypal.notify');



        try {
            $response = $this->gateway->purchase(array(
                'amount' => $item_amount,
                'currency' => $this->curr->name,
                'returnUrl' => $notify_url,
                'cancelUrl' => $cancel_url,
            ))->send();

            if ($response->isRedirect()) {
                Session::put('paypal_data', $sub);
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

        $paypal_data = Session::get('paypal_data');
        $success_url = route('user.payment.return');
        $cancel_url = route('user.payment.cancle');
        $input = $request->all();


        $responseData = $request->all();
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

            $order = new UserSubscription;
            $order->user_id = $paypal_data['user_id'];
            $order->subscription_id = $paypal_data['subscription_id'];
            $order->title = $paypal_data['title'];
            $order->currency_sign = $this->curr->sign;
            $order->currency_code = $this->curr->name;
            $order->currency_value = $this->curr->value;
            $order->price = $paypal_data['price'];
            $order->days = $paypal_data['days'];
            $order->allowed_products = $paypal_data['allowed_products'];
            $order->details = $paypal_data['details'];
            $order->method = $paypal_data['method'];
            $order->txnid = $response->getData()['transactions'][0]['related_resources'][0]['sale']['id'];
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

            Session::forget('payment_id');
            Session::forget('molly_data');
            Session::forget('user_data');
            Session::forget('order_data');

            return redirect($success_url);
        } else {
            return redirect($cancel_url);
        }
    }
}
