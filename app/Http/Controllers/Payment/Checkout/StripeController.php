<?php

namespace App\Http\Controllers\Payment\Checkout;

use App\{
    Models\Cart,
    Models\Order,
    Models\PaymentGateway,
    Classes\GeniusMailer
};
use App\Models\Country;
use App\Models\Generalsetting;
use App\Models\Reward;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use OrderHelper;
use Config;
use Str;

class StripeController extends CheckoutBaseControlller
{
    public function __construct()
    {
        parent::__construct();
        $data = PaymentGateway::whereKeyword('stripe')->first();
        $paydata = $data->convertAutoData();
        \Config::set('services.stripe.key', $paydata['key']);
        \Config::set('services.stripe.secret', $paydata['secret']);
    }

    public function store(Request $request)
    {
        $input = $request->all();


        if ($request->pass_check) {
            $auth = OrderHelper::auth_check($input); // For Authentication Checking
            if (!$auth['auth_success']) {
                return redirect()->back()->with('unsuccess', $auth['error_message']);
            }
        }

        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success', __("You don't have any product to checkout."));
        }

        try {
            $oldCart = Session::get('cart');
            // $cart = new Cart($oldCart);
            $cart=$oldCart;
            $gs = Generalsetting::first();
            $total = $request->total / $this->curr->value;
            $total = $total * $this->curr->value;


            $stripe_secret_key = Config::get('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                "success_url" => route('front.stripe.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                "cancel_url" => route('front.payment.cancle'),
                "locale" => "auto",
                "line_items" => [
                    [
                        "quantity" => $cart->totalQty,
                        "price_data" => [
                            "currency" => $this->curr->name,
                            "unit_amount" => round($total / $cart->totalQty, 2) * 100,
                            "product_data" => [
                                "name" => $gs->title . 'Payment'
                            ]
                        ]
                    ],

                ]
            ]);

            Session::put('input_data', $input);
            return redirect($checkout_session->url);
        } catch (Exception $e) {
            return back()->with('unsuccess', $e->getMessage());
        }
    }


    public function notify(Request $request)
    {

        $input = Session::get('input_data');
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);


        if ($response->status == 'complete') {
            $oldCart = Session::get('cart');
            $cart = new Cart($oldCart);
            OrderHelper::license_check($cart); // For License Checking
            $t_oldCart = Session::get('cart');
            $t_cart = new Cart($t_oldCart);
            $new_cart = [];
            $new_cart['totalQty'] = $t_cart->totalQty;
            $new_cart['totalPrice'] = $t_cart->totalPrice;
            $new_cart['items'] = $t_cart->items;
            $new_cart = json_encode($new_cart);
            $temp_affilate_users = \OrderHelper::product_affilate_check($cart); // For Product Based Affilate Checking
            $affilate_users = $temp_affilate_users == null ? null : json_encode($temp_affilate_users);


            $orderCalculate = \PriceHelper::getOrderTotal($input, $cart);

            if (isset($orderCalculate['success']) && $orderCalculate['success'] == false) {
                return redirect()->back()->with('unsuccess', $orderCalculate['message']);
            }

            if ($this->gs->multiple_shipping == 0) {
                $orderTotal = $orderCalculate['total_amount'];
                $shipping = $orderCalculate['shipping'];
                $packeing = $orderCalculate['packeing'];
                $is_shipping = $orderCalculate['is_shipping'];
                $vendor_shipping_ids = $orderCalculate['vendor_shipping_ids'];
                $vendor_packing_ids = $orderCalculate['vendor_packing_ids'];
                $vendor_ids = $orderCalculate['vendor_ids'];

                $input['shipping_title'] = $shipping->title;
                $input['vendor_shipping_id'] = $shipping->id;
                $input['packing_title'] = $packeing->title;
                $input['vendor_packing_id'] = $packeing->id;
                $input['shipping_cost'] = $packeing->price;
                $input['packing_cost'] = $packeing->price;
                $input['is_shipping'] = $is_shipping;
                $input['vendor_shipping_ids'] = $vendor_shipping_ids;
                $input['vendor_packing_ids'] = $vendor_packing_ids;
                $input['vendor_ids'] = $vendor_ids;
            } else {


                // multi shipping

                $orderTotal = $orderCalculate['total_amount'];
                $shipping = $orderCalculate['shipping'];
                $packeing = $orderCalculate['packeing'];
                $is_shipping = $orderCalculate['is_shipping'];
                $vendor_shipping_ids = $orderCalculate['vendor_shipping_ids'];
                $vendor_packing_ids = $orderCalculate['vendor_packing_ids'];
                $vendor_ids = $orderCalculate['vendor_ids'];
                $shipping_cost = $orderCalculate['shipping_cost'];
                $packing_cost = $orderCalculate['packing_cost'];

                $input['shipping_title'] = $vendor_shipping_ids;
                $input['vendor_shipping_id'] = $vendor_shipping_ids;
                $input['packing_title'] = $vendor_packing_ids;
                $input['vendor_packing_id'] = $vendor_packing_ids;
                $input['shipping_cost'] = $shipping_cost;
                $input['packing_cost'] = $packing_cost;
                $input['is_shipping'] = $is_shipping;
                $input['vendor_shipping_ids'] = $vendor_shipping_ids;
                $input['vendor_packing_ids'] = $vendor_packing_ids;
                $input['vendor_ids'] = $vendor_ids;
                unset($input['shipping']);
                unset($input['packeging']);
            }




            $order = new Order;
            $input['cart'] = $new_cart;
            $input['user_id'] = Auth::check() ? Auth::user()->id : NULL;
            $input['affilate_users'] = $affilate_users;
            $input['pay_amount'] = $orderTotal;
            $input['order_number'] = Str::random(4) . time();
            $input['wallet_price'] = $input['wallet_price'] / $this->curr->value;
            $input['payment_status'] = 'Completed';
            $input['txnid'] = $response->payment_intent;
            $input['method'] = 'Stripe';
            if ($input['tax_type'] == 'state_tax') {
                $input['tax_location'] = State::findOrFail($input['tax'])->state;
            } else {
                $input['tax_location'] = Country::findOrFail($input['tax'])->country_name;
            }
            $input['tax'] = Session::get('current_tax');

            if ($input['dp'] == 1) {
                $input['status'] = 'completed';
            }
            if (Session::has('affilate')) {
                $val = $input['total'] / $this->curr->value;
                $val = $val / 100;
                $sub = $val * $this->gs->affilate_charge;
                if ($temp_affilate_users != null) {
                    $t_sub = 0;
                    foreach ($temp_affilate_users as $t_cost) {
                        $t_sub += $t_cost['charge'];
                    }
                    $sub = $sub - $t_sub;
                }

                if ($sub > 0) {
                    OrderHelper::affilate_check(Session::get('affilate'), $sub, $input['dp']); // For Affiliate Checking
                    $input['affilate_user'] = Session::get('affilate');
                    $input['affilate_charge'] = $sub;
                }
            }
            // fake 
            $input['shipping'] = null;
            $input['packeging'] = null;


            $order->fill($input)->save();
            $order->tracks()->create(['title' => 'Pending', 'text' => 'You have successfully placed your order.']);
            $order->notifications()->create();

            if ($input['coupon_id'] != "") {
                OrderHelper::coupon_check($input['coupon_id']); // For Coupon Checking
            }


            if (Auth::check()) {
                if ($this->gs->is_reward == 1) {
                    $num = $order->pay_amount;
                    $rewards = Reward::get();
                    foreach ($rewards as $i) {
                        $smallest[$i->order_amount] = abs($i->order_amount - $num);
                    }

                    if (isset($smallest)) {
                        asort($smallest);
                        $final_reword = Reward::where('order_amount', key($smallest))->first();
                        Auth::user()->update(['reward' => (Auth::user()->reward + $final_reword->reward)]);
                    }
                }
            }

            OrderHelper::size_qty_check($cart); // For Size Quantiy Checking
            OrderHelper::stock_check($cart); // For Stock Checking
            OrderHelper::vendor_order_check($cart, $order); // For Vendor Order Checking

            Session::put('temporder', $order);
            Session::put('tempcart', $cart);
            Session::forget('cart');
            Session::forget('already');
            Session::forget('coupon');
            Session::forget('coupon_total');
            Session::forget('coupon_total1');
            Session::forget('coupon_percentage');

            if ($order->user_id != 0 && $order->wallet_price != 0) {
                OrderHelper::add_to_transaction($order, $order->wallet_price); // Store To Transactions
            }

            //Sending Email To Buyer
            $data = [
                'to' => $order->customer_email,
                'type' => "new_order",
                'cname' => $order->customer_name,
                'oamount' => "",
                'aname' => "",
                'aemail' => "",
                'wtitle' => "",
                'onumber' => $order->order_number,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendAutoOrderMail($data, $order->id);

            //Sending Email To Admin
            $data = [
                'to' => $this->ps->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
            return redirect(route('front.payment.return'));
        } else {
            return redirect(route('front.payment.cancle'));
        }
    }
}
