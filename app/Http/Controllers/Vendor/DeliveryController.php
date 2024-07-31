<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\{
    Models\Order
};
use App\Helpers\PriceHelper;
use App\Models\City;
use App\Models\DeliveryRider;
use App\Models\Package;
use App\Models\Rider;
use App\Models\RiderServiceArea;
use App\Models\Shipping;
use Datatables;

class DeliveryController extends VendorBaseController
{
    public function index()
    {
        return view('vendor.delivery.index');
    }

    //*** JSON Request
    public function datatables()
    {
        $user = $this->user;
        $datas = Order::orderby('id', 'desc')->with(array('vendororders' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }))->get()->reject(function ($item) use ($user) {
            if ($item->vendororders()->where('user_id', '=', $user->id)->count() == 0) {
                return true;
            }
            return false;
        });


        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('totalQty', function (Order $data) {
                return $data->vendororders()->where('user_id', '=', $this->user->id)->sum('qty');
            })
            ->editColumn('customer_info', function (Order $data) {
                $info = '<strong>' . __('Name') . ':</strong> ' . $data->customer_name . '<br>' .
                    '<strong>' . __('Email') . ':</strong> ' . $data->customer_email . '<br>' .
                    '<strong>' . __('Phone') . ':</strong> ' . $data->customer_phone . '<br>' .
                    '<strong>' . __('Country') . ':</strong> ' . $data->customer_country . '<br>' .
                    '<strong>' . __('City') . ':</strong> ' . $data->customer_city . '<br>' .
                    '<strong>' . __('Postal Code') . ':</strong> ' . $data->customer_zip . '<br>' .
                    '<strong>' . __('Address') . ':</strong> ' . $data->customer_address . '<br>' .
                    '<strong>' . __('Order Date') . ':</strong> ' . $data->created_at->diffForHumans() . '<br>';
                return $info;
            })


            ->editColumn('riders', function (Order $data) {
                $delivery =  DeliveryRider::where('order_id', $data->id)->whereVendorId(auth()->id())->first();

                if ($delivery) {
                    $message = '<strong class="display-5">Rider : ' . $delivery->rider->name . ' </br>Delivery Cost : ' . PriceHelper::showAdminCurrencyPrice($delivery->servicearea->price) . '</br> 
                    Pickup Point : ' . $delivery->pickup->location . '</br>
                    Status : 
                    <span class="badge badge-dark p-1">' . $delivery->status . '</span>
                    </strong>';
                    return $message;
                } else {
                    $message = '<span class="badge badge-danger p-1">' . __('Not Assigned') . '</span>';
                    return $message;
                }
            })

            ->editColumn('pay_amount', function (Order $data) {

                $order = Order::findOrFail($data->id);
                $user = $this->user;

                $price = $order->vendororders()->where('user_id', '=', $user->id)->sum('price');
                if ($order->is_shipping == 1) {
                    $vendor_shipping = json_decode($order->vendor_shipping_id);
                    $user_id = auth()->id();
                    // shipping cost
                    $shipping_id = $vendor_shipping->$user_id;
                    $shipping = Shipping::findOrFail($shipping_id);
                    if ($shipping) {
                        $price = $price + round($shipping->price * $order->currency_value, 2);
                    }

                    // packaging cost
                    $vendor_packing_id = json_decode($order->vendor_packing_id);
                    $packing_id = $vendor_packing_id->$user_id;
                    $packaging = Package::findOrFail($packing_id);
                    if ($packaging) {
                        $price = $price + round($packaging->price * $order->currency_value, 2);
                    }
                }


                return \PriceHelper::showOrderCurrencyPrice(($price), $data->currency_sign);
            })


            ->addColumn('action', function (Order $data) {
                $delevery = DeliveryRider::where('vendor_id', auth()->id())->where('order_id', $data->id)->first();
                if ($delevery && $delevery->status == 'delivered') {
                    $auction = '<div class="action-list">
                    <a href="' . route('vendor-order-show', $data->order_number) . '" class="btn btn-outline-primary btn-sm"><i class="fa fa-eye"></i> ' . __('Order View') . '</a>
                    </div>';
                } else {
                    $auction = '<div class="action-list">
                    <button data-toggle="modal" data-target="#riderList" customer-city="' . $data->customer_city . '" order_id="' . $data->id . '" class="mybtn1 searchDeliveryRider">
                    <i class="fa fa-user"></i>  ' . __("Assign Rider") . ' </button>
                    </div>';
                }


                return $auction;
            })
            ->rawColumns(['id', 'customer_info', 'riders', 'action','pay_amount'])
            ->toJson(); //--- Returning Json Data To Client Side

    }


    public function findReider(Request $request)
    {
        $city = City::where('city_name', $request->city)->first();
        $areas = RiderServiceArea::where('city_id', $city->id)->get();

        $ridersData = '
        <option value="">' . __('Select Rider') . '</option>
        ';
        foreach ($areas as $rider) {
            $ridersData .= '<option riderName="' . $rider->rider->name . '" area="' . $request->city . '" riderCost="' . PriceHelper::showAdminCurrencyPrice($rider->price) . '" value="' . $rider->id . '">' . $rider->rider->name . '-' . PriceHelper::showAdminCurrencyPrice($rider->price) . '</option>';
        }

        return response()->json(['riders' => $ridersData]);
    }


    public function findReiderSubmit(Request $request)
    {


        $delivery =  DeliveryRider::where('order_id', $request->order_id)->whereVendorId(auth()->id())->first();

        $service_area = RiderServiceArea::where('id', $request->rider_id)->first();

        if ($delivery) {
            $delivery->rider_id = $service_area->rider_id;
            $delivery->service_area_id = $service_area->id;
            $delivery->status = 'pending';
            $delivery->pickup_point_id = $request->pickup_point_id;
            $delivery->save();
        } else {
            $delivery = new DeliveryRider();
            $delivery->order_id = $request->order_id;
            $delivery->vendor_id = auth()->id();
            $delivery->rider_id = $service_area->rider_id;
            $delivery->service_area_id = $service_area->id;
            $delivery->pickup_point_id = $request->pickup_point_id;
            $delivery->status = 'pending';
            $delivery->save();
        }
        return response()->json(['success' => true, 'message' => __('Rider Assigned Successfully')]);
    }
}
