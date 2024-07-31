<?php

namespace App\Http\Controllers\Api\Front;

use App\Classes\GeniusMailer;
use App\Helpers\OrderHelper;
use App\Helpers\PriceHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailsResource;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\Order;
use App\Models\OrderTrack;
use App\Models\Package;
use App\Models\Pagesetting;
use App\Models\Product;
use App\Models\Reward;
use App\Models\Shipping;
use App\Models\State;
use App\Models\User;
use App\Models\VendorOrder;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Session;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {

        try {

            $datas = ['id', 'qty', 'size', 'size_qty', 'size_key', 'size_price', 'color', 'keys', 'values', 'prices'];
            $input = $request->all();

            $items = $input['items'];

            if (gettype($items) == 'string') {
                $items = json_decode($items, true);
            }


            // $new_cart = new Cart(null);
               $new_cart = Cart::restoreCart(null);

            foreach ($items as $key => $item) {
                if (array_keys($item) == $datas) {
                    $this->addtocart($new_cart, $input['currency_code'], $item['id'], $item['qty'], $item['size'], $item['color'], $item['size_qty'], $item['size_price'], $item['size_key'], $item['keys'], $item['values'], $item['prices'], $input['affilate_user']);
                }
            }


            // $cart = new Cart($new_cart);
               $cart = Cart::restoreCart($new_cart);

            $gs = Generalsetting::find(1);

            $currency_code = $input['currency_code'];

            if (!empty($currency_code)) {
                $curr = Currency::where('name', '=', $currency_code)->first();
                if (empty($curr)) {
                    $curr = Currency::where('is_default', '=', 1)->first();
                }
            } else {
                $curr = Currency::where('is_default', '=', 1)->first();
            }

            OrderHelper::license_check($cart); // For License Checking


            // $t_cart = new Cart($cart);
               $t_cart = Cart::restoreCart(null);
            $new_cart = [];
            $new_cart['totalQty'] = $t_cart->totalQty;
            $new_cart['totalPrice'] = $t_cart->totalPrice;
            $new_cart['items'] = $t_cart->items;
            $new_cart = json_encode($new_cart);



            $temp_affilate_users = OrderHelper::product_affilate_check($cart); // For Product Based Affilate Checking
            $affilate_users = $temp_affilate_users == null ? null : json_encode($temp_affilate_users);


            foreach ($cart->items as $key => $prod) {

                if (!empty($prod['item']['license']) && !empty($prod['item']['license_qty'])) {
                    foreach ($prod['item']['license_qty'] as $ttl => $dtl) {

                        if ($dtl != 0) {

                            $dtl--;
                            $produc = Product::find($prod['item']['id']);
                            $temp = $produc->license_qty;
                            $temp[$ttl] = $dtl;
                            $final = implode(',', $temp);
                            $produc->license_qty = $final;
                            $produc->update();
                            $temp = $produc->license;
                            $license = $temp[$ttl];
                            $cart->MobileupdateLicense($key, $license);
                        }
                    }
                }
            }

            // $t_cart = new Cart($cart);
               $t_cart = Cart::restoreCart($cart);


            $orderCalculate = PriceHelper::getOrderTotal($input, $t_cart);

            if (isset($orderCalculate['success']) && $orderCalculate['success'] == false) {
                return redirect()->back()->with('unsuccess', $orderCalculate['message']);
            }

            if ($gs->multiple_shipping == 0) {
                $orderTotal = $orderCalculate['total_amount'];
                $shipping = $orderCalculate['shipping'];
                $packeing = $orderCalculate['packeing'];
                $is_shipping = $orderCalculate['is_shipping'];
                $vendor_shipping_ids = $orderCalculate['vendor_shipping_ids'];
                $vendor_packing_ids = $orderCalculate['vendor_packing_ids'];
                $vendor_ids = $orderCalculate['vendor_ids'];

                $input['shipping_title'] = @$shipping->title;
                $input['vendor_shipping_id'] = @$shipping->id;
                $input['packing_title'] = @$packeing->title;
                $input['vendor_packing_id'] = @$packeing->id;
                $input['shipping_cost'] = @$shipping->price ?? 0;
                $input['packing_cost'] = @$packeing->price ?? 0;
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

            $input['user_id'] = $request->user_id ? $request->user_id : null;

            $input['cart'] = $new_cart;
            $input['affilate_users'] = $affilate_users;
            $input['currency_name'] = $curr->name;
            $input['currency_sign'] = $curr->sign;
            $input['currency_value'] = $curr->value;
            $input['pay_amount'] = $orderTotal / $curr->value;
            $input['order_number'] = Str::random(4) . time();
            $input['wallet_price'] = $request->wallet_price / $curr->value;

            if (@$input['tax_type'] == 'state_tax') {
                if (@$input['tax_type'] == 'state_tax') {
                    $input['tax_location'] = State::findOrFail($input['tax'])->state;
                } else {
                    $input['tax_location'] = Country::findOrFail($input['tax'])->country_name;
                }
            }



            $state = State::where('id', $input['tax'])->first();
            if ($state) {
                $total = $cart->totalPrice;
                $tax = $total * $state->tax / 100;
            }



            $input['tax'] = $tax ?? 0;

            if (Session::has('affilate')) {
                $val = $request->total / $curr->value;
                $val = $val / 100;
                $sub = $val * $gs->affilate_charge;
                if ($temp_affilate_users != null) {
                    $t_sub = 0;
                    foreach ($temp_affilate_users as $t_cost) {
                        $t_sub += $t_cost['charge'];
                    }
                    $sub = $sub - $t_sub;
                }
                if ($sub > 0) {
                    $user = OrderHelper::affilate_check(Session::get('affilate'), $sub, $input['dp']); // For Affiliate Checking
                    $input['affilate_user'] = Session::get('affilate');
                    $input['affilate_charge'] = $sub;
                }
            }

            $order->fill($input)->save();
            $order->tracks()->create(['title' => 'Pending', 'text' => 'You have successfully placed your order.']);
            $order->notifications()->create();

            if (Auth::guard('api')->check()) {
                if ($gs->is_reward == 1) {
                    $num = $order->pay_amount;
                    $rewards = Reward::get();
                   
                    $smallest=[];
                    foreach ($rewards as $i) {
                        $smallest[$i->order_amount] = abs($i->order_amount - $num);
                        

                    }

                    asort($smallest );

                    $final_reword = Reward::where('order_amount', key($smallest))->first();
                    if ($final_reword) {
                        Auth::guard('api')->user()->update(['reward' => (Auth::guard('api')->user()->reward + $final_reword->reward)]);
                    }
                }
            }


            if (Auth::guard('api')->check()) {
                Auth::guard('api')->user()->update(['balance' => (Auth::guard('api')->user()->balance - $order->wallet_price)]);
            }


            OrderHelper::size_qty_check($cart); // For Size Quantiy Checking
            OrderHelper::stock_check($cart); // For Stock Checking
            OrderHelper::vendor_order_check($cart, $order); // For Vendor Order Checking

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
            $ps = Pagesetting::find(1);
            //Sending Email To Admin
            $data = [
                'to' => $ps->contact_email,
                'subject' => "New Order Recieved!!",
                'body' => "Hello Admin!<br>Your store has received a new order.<br>Order Number is " . $order->order_number . ".Please login to your panel to check. <br>Thank you.",
            ];
            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);

            unset($order['cart']);
            return response()->json(['status' => true, 'data' => route('payment.checkout') . '?order_number=' . $order->order_number, 'error' => []]);
        } 
        
        catch (\Exception $e) {
            
            return response()->json(['status' => false, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    //*** POST Request
    public function update(Request $request, $id)
    {

        try {
            //--- Logic Section
            $data = Order::find($id);

            $input = $request->all();
            if ($data->status == "completed") {

                // Then Save Without Changing it.
                $input['status'] = "completed";
                $data->update($input);
                //--- Logic Section Ends

                //--- Redirect Section
                return response()->json(['status' => true, 'data' => $data, 'error' => []]);
                //--- Redirect Section Ends

            } else {
                if ($input['status'] == "completed") {

                    foreach ($data->vendororders as $vorder) {
                        $uprice = User::find($vorder->user_id);
                        $uprice->current_balance = $uprice->current_balance + $vorder->price;
                        $uprice->update();
                    }

                    if (User::where('id', $data->affilate_user)->exists()) {
                        $auser = User::where('id', $data->affilate_user)->first();
                        $auser->affilate_income += $data->affilate_charge;
                        $auser->update();
                    }

                    $gs = Generalsetting::find(1);
                    if ($gs->is_smtp == 1) {
                        $maildata = [
                            'to' => $data->customer_email,
                            'subject' => 'Your order ' . $data->order_number . ' is Confirmed!',
                            'body' => "Hello " . $data->customer_name . "," . "\n Thank you for shopping with us. We are looking forward to your next visit.",
                        ];

                        $mailer = new GeniusMailer();
                        $mailer->sendCustomMail($maildata);
                    } else {
                        $to = $data->customer_email;
                        $subject = 'Your order ' . $data->order_number . ' is Confirmed!';
                        $msg = "Hello " . $data->customer_name . "," . "\n Thank you for shopping with us. We are looking forward to your next visit.";
                        $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                        mail($to, $subject, $msg, $headers);
                    }
                }
                if ($input['status'] == "declined") {

                    if ($data->user_id != 0) {
                        if ($data->wallet_price != 0) {
                            $user = User::find($data->user_id);
                            if ($user) {
                                $user->balance = $user->balance + $data->wallet_price;
                                $user->save();
                            }
                        }
                    }

                    $cart = unserialize(bzdecompress(utf8_decode($data->cart)));

                    foreach ($cart->items as $prod) {
                        $x = (string) $prod['stock'];
                        if ($x != null) {

                            $product = Product::find($prod['item']['id']);
                            $product->stock = $product->stock + $prod['qty'];
                            $product->update();
                        }
                    }

                    foreach ($cart->items as $prod) {
                        $x = (string) $prod['size_qty'];
                        if (!empty($x)) {
                            $product = Product::find($prod['item']['id']);
                            $x = (int) $x;
                            $temp = $product->size_qty;
                            $temp[$prod['size_key']] = $x;
                            $temp1 = implode(',', $temp);
                            $product->size_qty = $temp1;
                            $product->update();
                        }
                    }

                    $gs = Generalsetting::find(1);
                    if ($gs->is_smtp == 1) {
                        $maildata = [
                            'to' => $data->customer_email,
                            'subject' => 'Your order ' . $data->order_number . ' is Declined!',
                            'body' => "Hello " . $data->customer_name . "," . "\n We are sorry for the inconvenience caused. We are looking forward to your next visit.",
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendCustomMail($maildata);
                    } else {
                        $to = $data->customer_email;
                        $subject = 'Your order ' . $data->order_number . ' is Declined!';
                        $msg = "Hello " . $data->customer_name . "," . "\n We are sorry for the inconvenience caused. We are looking forward to your next visit.";
                        $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                        mail($to, $subject, $msg, $headers);
                    }
                }

                $data->update($input);

                if ($request->track_text) {
                    $title = ucwords($request->status);
                    $ck = OrderTrack::where('order_id', '=', $id)->where('title', '=', $title)->first();
                    if ($ck) {
                        $ck->order_id = $id;
                        $ck->title = $title;
                        $ck->text = $request->track_text;
                        $ck->update();
                    } else {
                        $data = new OrderTrack;
                        $data->order_id = $id;
                        $data->title = $title;
                        $data->text = $request->track_text;
                        $data->save();
                    }
                }

                VendorOrder::where('order_id', '=', $id)->update(['status' => $input['status']]);

                //--- Redirect Section
                return response()->json(['status' => true, 'data' => $data, 'error' => []]);
                //--- Redirect Section Ends

            }
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    //*** POST Request
    public function delete($id)
    {

        try {
            //--- Logic Section
            $data = Order::find($id);
            if ($data) {
                $data->delete();

                //--- Redirect Section
                return response()->json(['status' => true, 'data' => 'Order Deleted Successfully', 'error' => []]);
                //--- Redirect Section Ends
            } else {
                return response()->json(['status' => false, 'data' => [], 'error' => ['message' => 'Order Not Found']]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    //*** GET Request
    public function orderDetails(Request $request)
    {
        try {
            if ($request->has('order_number')) {
                $order_number = $request->order_number;
                $order = Order::where('order_number', $order_number)->firstOrFail();
                return response()->json(['status' => true, 'data' => new OrderDetailsResource($order), 'error' => []]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'data' => [], 'error' => ['message' => $e->getMessage()]]);
        }
    }

    protected function addtocart($cart, $currency_code, $p_id, $p_qty, $p_size, $p_color, $p_size_qty, $p_size_price, $p_size_key, $p_keys, $p_values, $p_prices, $affilate_user)
    {

        try {

            $id = $p_id;
            $qty = $p_qty;
            $size = str_replace(' ', '-', $p_size);
            $color = $p_color;
            $size_qty = $p_size_qty;
            $size_price = (float) $p_size_price;
            $size_key = $p_size_key;
            $keys = $p_keys;
            $keys = explode(",", $keys);
            $values = $p_values;
            $values = explode(",", $values);
            $prices = $p_prices;

            if (!empty($prices)) {
                $prices = explode(",", $prices);
            }

            $keys = $keys == "" ? '' : implode(',', $keys);

            $values = $values == "" ? '' : implode(',', $values);
            if (!empty($currency_code)) {
                $curr = Currency::where('name', '=', $currency_code)->first();
                if (!empty($curr)) {
                    $curr = Currency::where('is_default', '=', 1)->first();
                }
            } else {
                $curr = Currency::where('is_default', '=', 1)->first();
            }

            $size_price = ($size_price / $curr->value);
            $prod = Product::where('id', '=', $id)->first(['id', 'user_id', 'slug', 'name', 'photo', 'size', 'size_qty', 'size_price', 'color', 'price', 'stock', 'type', 'file', 'link', 'license', 'license_qty', 'measure', 'whole_sell_qty', 'whole_sell_discount', 'attributes']);

            if ($prod->user_id != 0) {
                $gs = Generalsetting::find(1);
                $prc = $prod->price + $gs->fixed_commission + ($prod->price / 100) * $gs->percentage_commission;
                $prod->price = round($prc, 2);
            }

            if (!empty($prices)) {
                if (!empty($prices[0])) {
                    foreach ($prices as $data) {
                        $prod->price += ($data / $curr->value);
                    }
                }
            }

            if (!empty($prod->license_qty)) {
                $lcheck = 1;
                foreach ($prod->license_qty as $ttl => $dtl) {
                    if ($dtl < 1) {
                        $lcheck = 0;
                    } else {
                        $lcheck = 1;
                        break;
                    }
                }
                if ($lcheck == 0) {
                    return false;
                }
            }
            if (empty($size)) {
                if (!empty($prod->size)) {
                    $size = trim($prod->size[0]);
                }
                $size = str_replace(' ', '-', $size);
            }

            if (empty($color)) {
                if (!empty($prod->color)) {
                    $color = $prod->color[0];
                }
            }


            $color = str_replace('#', '', $color);

            $cart->addnum($prod, $prod->id, $qty, $size, $color, $size_qty, $size_price, $size_key, $keys, $values, $affilate_user);

            $cart->totalPrice = 0;


            foreach ($cart->items as $data) {
                $cart->totalPrice += $data['price'];
            }

            return $cart->items;
        } catch (\Exception $e) {
        }
    }

    public function getCoupon(Request $request)
    {

        $code = $request->coupon;
        $coupon = Coupon::where('code', '=', $code)->where('status', 1)->first();

        if ($coupon) {

            $today = date('Y-m-d');
            $from = date('Y-m-d', strtotime($coupon->start_date));

            $to = date('Y-m-d', strtotime($coupon->end_date));

            if ($from <= $today && $to >= $today) {
                return response()->json(['status' => true, 'data' => $coupon, 'error' => []]);
            } else {
                return response()->json(['status' => false, 'data' => [], 'error' => 'Invalid Coupon']);
            }
        } else {
            return response()->json(['status' => false, 'data' => [], 'error' => 'Coupon Not Found']);
        }
    }

    public function getShippingPackaging()
    {
        $shipping = Shipping::whereUserId(0)->get();
        $packaging = Package::whereUserId(0)->get();
        return response()->json(['status' => true, 'data' => ['shipping' => $shipping, 'packaging' => $packaging], 'error' => []]);
    }


    public function VendorWisegetShippingPackaging(Request $request)
    {

        $explode = explode(',', $request->vendor_ids);

        foreach ($explode as $key => $value) {
            $shipping[$value] = Shipping::where('user_id', $value)->get();
            $packaging[$value] = Package::where('user_id', $value)->get();
        }


        return response()->json(['status' => true, 'data' => ['shipping' => $shipping, 'packaging' => $packaging], 'error' => []]);
    }


    public function countries()
    {
        $countries = Country::with('states.cities')->get();
        return response()->json(['status' => true, 'data' => $countries, 'error' => []]);
    }
}
