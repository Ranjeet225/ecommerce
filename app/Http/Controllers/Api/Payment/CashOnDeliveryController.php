<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CashOnDeliveryController extends Controller
{

    public function store(Request $request)
    {
        if ($request->has('order_number')) {
            $order_number = $request->order_number;
            $order = Order::where('order_number', $order_number)->firstOrFail();
            $item_amount = $order->pay_amount * $order->currency_value;
            $order->pay_amount = round($item_amount / $order->currency_value, 2);
            $order->method = $request->method;
            $order->txnid = Str::random(12);
            $order->payment_status = 'Pending';
            $order->save();
            return redirect(route('front.payment.success', 1));
        } else {
            return redirect()->back()->with('unsuccess', 'Something Went Wrong.');
        }
    }

}
