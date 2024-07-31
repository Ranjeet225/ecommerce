<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Payment\Checkout\CheckoutBaseControlller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Omnipay\Omnipay;

class PaypalController extends CheckoutBaseControlller
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
        // dd($request->all(),1);
        $order_number = $request->order_number;
        $order = Order::where('order_number', $order_number)->firstOrFail();
        $curr = Currency::where('sign', '=', $order->currency_sign)->firstOrFail();
        if ($curr->name != "USD") {
            return redirect()->back()->with('unsuccess', 'Please Select USD Currency For Paypal.');
        }
        $item_amount = round($order->pay_amount * $order->currency_value, 2);

        $cancel_url = route('api.paypal.cancle');
        $notify_url = route('api.paypal.notify');


        try {
            $response = $this->gateway->purchase(array(
                'amount' => $item_amount,
                'currency' => $curr->name,
                'returnUrl' => $notify_url,
                'cancelUrl' => $cancel_url,
            ))->send();

            if ($response->isRedirect()) {
                Session::put('order_number', $order_number);
                if ($response->redirect()) {
                    return redirect($response->redirect());
                }
            } else {
                return redirect()->back()->with('unsuccess', $response->getMessage());
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('unsuccess', $th->getMessage());
        }


        return redirect()->back()->with('unsuccess', __('Unknown error occurred'));
    }

    public function notify(Request $request)
    {

        $responseData = $request->all();
        $order_number = Session::get('order_number');

        $success_url = route('front.payment.success', 1);
        $cancel_url = route('payment.checkout') . "?order_number=" . $order_number;

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
            $order = Order::where('order_number', $order_number)->firstOrFail();
            $data['payment_status'] = 'Completed';
            $order->method = "Paypal";
            $data['txnid'] = $response->getData()['transactions'][0]['related_resources'][0]['sale']['id'];
            $order->update($data);
            return redirect($success_url);
        }
        return redirect($cancel_url);
    }
}
