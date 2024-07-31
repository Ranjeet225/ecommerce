<?php

namespace App\Http\Controllers\Payment\Checkout;

use App\{
    Models\Cart,
    Models\Order,
    Models\PaymentGateway,
    Classes\GeniusMailer
};
use App\Helpers\PriceHelper;
use App\Models\Country;
use App\Models\Reward;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use OrderHelper;
use Razorpay\Api\Api;
use Illuminate\Support\Str;

class RazorpayController extends CheckoutBaseControlller
{
    public function __construct()
    {
        parent::__construct();
        $data = PaymentGateway::whereKeyword('razorpay')->first();
        $paydata = $data->convertAutoData();
        $this->keyId = $paydata['key'];
        $this->keySecret = $paydata['secret'];
        $this->displayCurrency = 'INR';
        $this->api = new Api($this->keyId, $this->keySecret);
    }


    public function store(Request $request)
    {
        $input = $request->all();
    //    print_r($input);
        $data = PaymentGateway::whereKeyword('razorpay')->first();
        $total = $request->total;


        if($this->curr->name != "INR")
        {
            return redirect()->back()->with('unsuccess',__('Please Select INR Currency For This Payment.'));
        }
        if($request->pass_check) {
            $auth = OrderHelper::auth_check($input); // For Authentication Checking
            if(!$auth['auth_success']){
                return redirect()->back()->with('unsuccess',$auth['error_message']);
            }
        }

        if (!Session::has('cart')) {
            return redirect()->route('front.cart')->with('success',__("You don't have any product to checkout."));
        }

        $order['item_name'] = $this->gs->title." Order";
        $order['item_number'] = Str::random(4).time();
        $order['item_amount'] = round($total,2);
        $cancel_url = route('front.payment.cancle');
        $notify_url = route('front.razorpay.notify');
// print_r($input);
    //  echo "<pre>";  print_r(Session::get('cart'));
        $total = PriceHelper::getOrderTotalAmount($input, Session::get('cart'));
      
//  dd($total);
        $orderData = [
            'receipt'         => $order['item_number'],
            'amount'          => $total * 100, // 2000 rupees in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];

        //  dd($orderData);

        $razorpayOrder = $this->api->order->create($orderData);
   // dd( $razorpayOrder);
        Session::put('input_data',$input);
        Session::put('order_data',$order);
        Session::put('order_payment_id', $razorpayOrder['id']);

        $displayAmount = $amount = $orderData['amount'];

        if ($this->displayCurrency !== 'INR')
        {
            $url = "https://api.fixer.io/latest?symbols=$this->displayCurrency&base=INR";
            $exchange = json_decode(file_get_contents($url), true);

            $displayAmount = $exchange['rates'][$this->displayCurrency] * $amount / 100;
        }

        $checkout = 'automatic';

        if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true))
        {
            $checkout = $_GET['checkout'];
        }

        $data = [
            "key"               => $this->keyId,
            "amount"            => $amount,
            "name"              => $order['item_name'],
            "description"       => $order['item_name'],
            "prefill"           => [
                "name"              => $request->customer_name,
                "email"             => $request->customer_email,
                "contact"           => $request->customer_phone,
            ],
            "notes"             => [
                "address"           => $request->customer_address,
                "merchant_order_id" => $order['item_number'],
            ],
            "theme"             => [
                "color"             => "{{$this->gs->colors}}"
            ],
            "order_id"          => $razorpayOrder['id'],
        ];

        if ($this->displayCurrency !== 'INR')
        {
            $data['display_currency']  = $this->displayCurrency;
            $data['display_amount']    = $displayAmount;
        }

        $json = json_encode($data);
        $displayCurrency = $this->displayCurrency;


        view()->share('langg', $this->language);
        return view( 'frontend.razorpay-checkout', compact( 'data','displayCurrency','json','notify_url' ) );
    }

    public function notify(Request $request)
    {
        $input = Session::get('input_data');
        $order_data = Session::get('order_data');
        $success_url = route('front.payment.return');
        $cancel_url = route('front.payment.cancle');
        $input_data = $request->all();
        /** Get the payment ID before session clear **/
        $payment_id = Session::get('order_payment_id');

        $success = true;

        if (empty($input_data['razorpay_payment_id']) === false)
        {

            try
            {
                $attributes = array(
                    'razorpay_order_id' => $payment_id,
                    'razorpay_payment_id' => $input_data['razorpay_payment_id'],
                    'razorpay_signature' => $input_data['razorpay_signature']
                );

                $this->api->utility->verifyPaymentSignature($attributes);
            }
            catch(SignatureVerificationError $e)
            {
                $success = false;
            }
        }

        if ($success === true){

                $oldCart = Session::get('cart');
                // $cart = new Cart($oldCart);

        $cart = Cart::restoreCart($oldCart);

                OrderHelper::license_check($cart); // For License Checking
                $t_oldCart = Session::get('cart');
                // $t_cart = new Cart($t_oldCart);

                $t_cart = Cart::restoreCart($t_oldCart);

                $new_cart = [];
                $new_cart['totalQty'] = $t_cart->totalQty;
                $new_cart['totalPrice'] = $t_cart->totalPrice;
                $new_cart['items'] = $t_cart->items;
                $new_cart = json_encode($new_cart);
                $temp_affilate_users = OrderHelper::product_affilate_check($cart); // For Product Based Affilate Checking
                $affilate_users = $temp_affilate_users == null ? null : json_encode($temp_affilate_users);

                $orderCalculate = PriceHelper::getOrderTotal($input, $cart);

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
                $input['order_number'] = $order_data['item_number'];
                $input['wallet_price'] = $input['wallet_price'] / $this->curr->value;
                $input['payment_status'] = "Completed";
                $input['txnid'] = $input_data['razorpay_payment_id'];

                if($input['tax_type'] == 'state_tax'){
                    $input['tax_location'] = State::findOrFail($input['tax'])->state;
                }else{
                    $input['tax_location'] = Country::findOrFail($input['tax'])->country_name;
                }
                $input['tax'] = Session::get('current_tax');


                if($input['dp'] == 1){
                    $input['status'] = 'completed';
                }
                if (Session::has('affilate')) {
                    $val = $request->total / $this->curr->value;
                    $val = $val / 100;
                    $sub = $val * $this->gs->affilate_charge;
                    if($temp_affilate_users != null){
                        $t_sub = 0;
                        foreach($temp_affilate_users as $t_cost){
                            $t_sub += $t_cost['charge'];
                        }
                        $sub = $sub - $t_sub;
                    }
                    if($sub > 0){
                        $user = OrderHelper::affilate_check(Session::get('affilate'),$sub,$input['dp']); // For Affiliate Checking
                        $input['affilate_user'] = Session::get('affilate');
                        $input['affilate_charge'] = $sub;
                    }

                }

                $order->fill($input)->save();
                $order->tracks()->create(['title' => 'Pending', 'text' => 'You have successfully placed your order.' ]);
                $order->notifications()->create();

                if($input['coupon_id'] != "") {
                    OrderHelper::coupon_check($input['coupon_id']); // For Coupon Checking
                }

                OrderHelper::size_qty_check($cart); // For Size Quantiy Checking
                OrderHelper::stock_check($cart); // For Stock Checking
                OrderHelper::vendor_order_check($cart,$order); // For Vendor Order Checking

                Session::put('temporder',$order);
                Session::put('tempcart',$cart);
                Session::forget('cart');
                Session::forget('already');
                Session::forget('coupon');
                Session::forget('coupon_total');
                Session::forget('coupon_total1');
                Session::forget('coupon_percentage');

                if ($order->user_id != 0 && $order->wallet_price != 0) {
                    OrderHelper::add_to_transaction($order,$order->wallet_price); // Store To Transactions
                }

                if(Auth::check()){
                    if($this->gs->is_reward == 1){
                        $num = $order->pay_amount;
                        $rewards = Reward::get();
                        foreach ($rewards as $i) {
                            $smallest[$i->order_amount] = abs($i->order_amount - $num);
                        }

                        if(isset($smallest)){
                            asort($smallest);
                      $final_reword = Reward::where('order_amount', key($smallest))->first();
                      Auth::user()->update(['reward' => (Auth::user()->reward + $final_reword->reward)]);
                      }
                    }
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
                $mailer->sendAutoOrderMail($data,$order->id);

                //Sending Email To Admin
                $data = [
                    'to' => $this->ps->contact_email,
                    'subject' => "New Order Recieved!!",
                    'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is ".$order->order_number.".Please login to your panel to check. <br>Thank you.",
                ];
                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);

                return redirect($success_url);

        }
        return redirect($cancel_url);
    }

}
