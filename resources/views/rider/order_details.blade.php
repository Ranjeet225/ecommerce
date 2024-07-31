@extends('layouts.front')

@section('content')
@include('partials.global.common-header')

<!-- breadcrumb -->
<div class="full-row bg-light overlay-dark py-5"
    style="background-image: url({{ $gs->breadcrumb_banner ? asset('assets/images/'.$gs->breadcrumb_banner):asset('assets/images/noimage.png') }}); background-position: center center; background-size: cover;">
    <div class="container">
        <div class="row text-center text-white">
            <div class="col-12">
                <h3 class="mb-2 text-white">{{ __('Delivery Items') }}</h3>
            </div>
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 d-inline-flex bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ ('rider-dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Delivery Items') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb -->

@php
$order = $data->order;
@endphp
<!--==================== Blog Section Start ====================-->
<div class="full-row">
    <div class="container">
        <div class="mb-4 d-xl-none">
            <button class="dashboard-sidebar-btn btn bg-primary rounded">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-xl-3">
                @include('partials.rider.dashboard-sidebar')
            </div>
            <div class="col-xl-9">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="widget border-0 p-40 widget_categories bg-light account-info">

                            <h4 class="widget-title down-line mb-30">{{ __('Delivery Details') }}
                                @if ($data->status == 'pending')
                                <a class="mybtn1 alert_link"
                                    href="{{route('rider-order-delivery-accept',$data->id)}}">@lang('Accept')</a>
                                <a class="mybtn1 alert_link"
                                    href="{{route('rider-order-delivery-reject',$data->id)}}">@lang('Reject')</a>

                                @elseif($data->status == 'accepted')
                                <a class="mybtn1 alert_link"
                                    href="{{route('rider-order-delivery-complete',$data->id)}}">@lang('Make
                                    Delivered')</a>

                                @elseif($data->status == 'rejected')
                                <strong class="bg-danger p-2 text-white">{{ __('Rejected') }}</strong>

                                @else
                                <strong class="bg-success p-2 text-white">{{ __('Delivered') }}</strong>
                                @endif
                            </h4>


                            <div class="view-order-page">

                                <div class="billing-add-area">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>{{ __('Delivery Address') }}</h5>
                                            <address>
                                                {{ __('Name:') }} {{$order->customer_name}}<br>
                                                {{ __('Email:') }} {{$order->customer_email}}<br>
                                                {{ __('Phone:') }} {{$order->customer_phone}}<br>
                                                {{ __('City:') }} {{$order->customer_city}}<br>
                                                {{ __('Address:') }} {{$order->customer_address}}<br>
                                                {{$order->customer_city}}-{{$order->customer_zip}}
                                            </address>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>{{ __('Vendor Information') }}</h5>
                                            <p>
                                                {{ __('Shop Name:') }} {{$data->vendor->shop_name}}<br>
                                                {{ __('Email:') }} {{$data->vendor->email}}<br>
                                                {{ __('Phone:') }} {{$data->vendor->phone}}<br>
                                                {{ __('City:') }} {{$data->vendor->city}}<br>
                                                {{ __('Address:') }} {{$data->vendor->address}}<br>
                                                <strong>{{ __('Pickup Location:') }}
                                                    {{$data->pickup->location}}</strong><br>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <div class="table-responsive">
                                    <h5>{{ __('Ordered Products:') }}</h5>
                                    <table class="table veiw-details-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('ID#') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Details') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $extra_price = 0;
                                            @endphp
                                            @foreach(json_decode($order->cart,true)['items'] as $product)
                                            @if ($product['user_id'] == $data->vendor_id)
                                            <tr>
                                                <td data-label="{{ __('ID#') }}">
                                                    <div>
                                                        {{ $product['item']['id'] }}
                                                    </div>
                                                </td>
                                                <td data-label="{{ __('Name') }}">
                                                    {{mb_strlen($product['item']['name'],'UTF-8') > 50 ?
                                                    mb_substr($product['item']['name'],0,50,'UTF-8').'...' :
                                                    $product['item']['name']}}

                                                </td>
                                                <td data-label="{{ __('Details') }}">
                                                    <div>
                                                        <b>{{ __('Quantity') }}</b>: {{$product['qty']}} <br>
                                                        @if(!empty($product['size']))
                                                        <b>{{ __('Size') }}</b>: {{ $product['item']['measure']
                                                        }}{{str_replace('-',' ',$product['size'])}} <br>
                                                        @endif
                                                        @if(!empty($product['color']))
                                                        <div class="d-flex mt-2">
                                                            <b>{{ __('Color') }}</b>: <span id="color-bar"
                                                                style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; border-radius: 50%; background: #{{$product['color']}};"></span>
                                                        </div>
                                                        @endif
                                                        @if(!empty($product['keys']))
                                                        @foreach( array_combine(explode(',', $product['keys']),
                                                        explode(',', $product['values'])) as $key => $value)
                                                        <b>{{ ucwords(str_replace('_', ' ', $key)) }} : </b> {{ $value
                                                        }} <br>
                                                        @endforeach
                                                        @endif
                                                    </div>
                                                </td>

                                            </tr>

                                            @endif
                                            @endforeach

                                        </tbody>
                                    </table>

                                    <div class="text-center">


                                        @php

                                        $order_shipping = @json_decode($data->order->vendor_shipping_id, true);
                                        $order_package = @json_decode($data->order->vendor_packing_id, true);
                                        $vendor_shipping_id = @$order_shipping[$data->vendor_id];
                                        $vendor_package_id = @$order_package[$data->vendor_id];
                                        if($vendor_shipping_id){
                                        $shipping = App\Models\Shipping::findOrFail($vendor_shipping_id);
                                        }else{
                                        $shipping = [];
                                        }
                                        if($vendor_package_id){
                                        $package = App\Models\Package::findOrFail($vendor_package_id);
                                        }else{
                                        $package = [];
                                        }

                                        $shipping_cost = 0;
                                        $packing_cost = 0;
                                        if($shipping){
                                        $shipping_cost = $shipping->price;
                                        }
                                        if($package){
                                        $packing_cost = $package->price;
                                        }

                                        $extra_price = $shipping_cost + $packing_cost;

                                        @endphp

                                        <strong>

                                            @lang('Collection Amount from Customer') :
                                            @if ($order->method == 'Cash On Delivery')
                                            {{
                                            \PriceHelper::showAdminCurrencyPrice((($order->vendororders->where('user_id',$data->vendor_id)->sum('price')+$extra_price)
                                            * $data->order->currency_value),$order->currency_sign) }}
                                            @else
                                            {{ __('N/A') }}
                                            @endif


                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <a class="back-btn theme-bg" href="{{ route('rider-orders') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@includeIf('partials.global.common-footer')

@endsection

@section('script')

<script>
    $(document).on('click','.alert_link',function(){
        var status = confirm('Are you sure? You want to perform this action');
        if(status == true){
            return true;
        }else{
            return false;
        }
    })
</script>
@endsection