<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{

    public function store(Request $request)
    {


        if ($request->has('order_number')) {
            $order_number = $request->order_number;
            $order = Order::where('order_number', $order_number)->firstOrFail();
            $item_amount = $order->pay_amount * $order->currency_value;
            $user = User::findOrFail($order->user_id);
            if ($order->user_id == 0) {
                return redirect()->back()->with('unsuccess', 'Please login to continue');
            } else {
                if ($user->balance < $item_amount) {
                    return redirect()->back()->with('unsuccess', 'You do not have enough balance in your wallet');
                }
            }

            $order->pay_amount = round($item_amount / $order->currency_value, 2);
            $order->method = 'Wallet';
            $order->txnid = Str::random(12);
            $order->payment_status = 'Completed';
            $order->save();

            $user->balance = $user->balance - $item_amount;
            $user->save();



            return redirect(route('front.payment.success', 1));
        } else {
            return redirect()->back()->with('unsuccess', 'Something Went Wrong.');
        }
    }
}
