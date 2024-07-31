@extends('layouts.admin')
     
@section('styles')

<style type="text/css">
    .order-table-wrap table#example2 {
    margin: 10px 20px;
}

</style>

@endsection


@section('content')
 

<div class="content-area">
    <div class="mr-breadcrumb">
       <div class="row">
          <div class="col-lg-12">
             <h4 class="heading">{{ __('Order Details') }} <a class="add-btn" href="javascript:history.back();"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h4>
             <ul class="links">
                <li>
                   <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                </li>
                <li>
                   <a href="javascript:;">{{ __('Orders') }}</a>
                </li>
                <li>
                   <a href="javascript:;">{{ __('Order Details') }}</a>
                </li>
             </ul>
          </div>
       </div>
    </div>
    <div class="order-table-wrap">
       <div class="row">
        
          <div class="col-lg-12 order-details-table">
            <div class="mr-table">
                <h4 class="title">
                    {{ __('Products') }}
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead>
                           <tr>
                           <tr>
                              <th>{{ __('Product ID#') }}</th>
                              <th>{{ __('Product Title') }}</th>
                              <th>{{ __('Price') }}</th>
                              <th>{{ __('Details') }}</th>
                              <th>{{ __('Subtotal') }}</th>
                           </tr>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($cart->items as $key1 => $product)
                          
                           <tr>
                              <td><input type="hidden" value="{{$key1}}">{{ $product['item']['id'] }}</td>
                              <td>
                                <img src="{{asset('assets/images/products/'.$product['item']['photo'])}}" alt="">
                                <br>
                                 <input type="hidden" value="{{ $product['license'] }}">
                                <a target="_blank" href="{{ route('front.product', $product['item']['slug']) }}">{{mb_strlen($product['item']['name'],'utf-8') > 30 ? mb_substr($product['item']['name'],0,30,'utf-8').'...' : $product['item']['name']}}</a>
                              </td>
                              <td class="product-price">
                                 <span>{{ App\Models\Product::convertPrice($product['item_price']) }}
                                 </span>
                              </td>
                              <td>
                                 @if($product['size'])
                                 <p>
                                    <strong>{{ __('Size') }} :</strong> {{str_replace('-',' ',$product['size'])}}
                                 </p>
                                 @endif
                                 @if($product['color'])
                                 <p>
                                    <strong>{{ __('color') }} :</strong> <span
                                       style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; border-radius: 50%; background: #{{$product['color']}};"></span>
                                 </p>
                                 @endif
                                 <p>
                                    <strong>{{ __('Qty') }} :</strong> {{$product['qty']}} {{ $product['item']['measure'] }}
                                 </p>
                                 @if(!empty($product['keys']))
                                 @foreach( array_combine(explode(',', $product['keys']), explode(',', $product['values']))  as $key => $value)
                                 <p>
                                    <b>{{ ucwords(str_replace('_', ' ', $key))  }} : </b> {{ $value }} 
                                 </p>
                                 @endforeach
                                 @endif
                              </td>
                              <td class="product-subtotal">
                                 <p class="d-inline-block"
                                    id="prc{{$product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values'])}}">
                                    {{ App\Models\Product::convertPrice($product['price']) }}
                                 </p>
                                 @if ($product['discount'] != 0)
                                 <strong>{{$product['discount']}} %{{__('off')}}</strong>
                                 @endif
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
          <div class="col-lg-8 my-5">
             <div class="special-box">
                <div class="heading-area">
                   <h4 class="title">
                      {{ __('Customer Details') }} 
                   </h4>
                </div>
                <div class="table-responsive-sm">
                   <table class="table">
                      <tbody>
                         <tr>
                            <th width="45%">{{ __('Name') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_name']}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Email') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_email']}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Phone') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_phone']}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Address') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_address']}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Country') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_country'] ? $address['customer_country'] : '--'}}</td>
                         </tr>
                         @if(@$address['customer_city'] != null)
                         <tr>
                            <th width="45%">{{ __('State') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_state'] ? $address['customer_state'] : '--'}}</td>
                         </tr>
                         @endif
                         <tr>
                            <th width="45%">{{ __('City') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_city'] ? $address['customer_city'] : '--'}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Postal Code') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$address['customer_zip'] ? $address['customer_zip'] : '--'}}</td>
                         </tr>
                      </tbody>
                   </table>
                </div>
             </div>
          </div>
          <div class="col-lg-4 my-5 ">
             <div class="special-box">
                <div class="heading-area">
                   <h4 class="title">
                      {{ __('Order Details') }} 
                   </h4>
                </div>
            
                <div class="table-responsive-sm">
                   <table class="table">
                      <tbody>
                         <tr>
                            <th width="45%">{{ __('Total Products') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{count($cart->items)}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Total Quintity') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{$cart->totalQty}}</td>
                         </tr>
                         <tr>
                            <th width="45%">{{ __('Total Amount') }}</th>
                            <th width="10%">:</th>
                            <td width="45%">{{App\Models\Product::convertPrice($cart->totalPrice)}}</td>
                         </tr>
                         <tr>
                            <td>
                                <a href="{{route('admin-order-create-submit')}}" class="mybtn1">Order Submit</a>
                            </td>
                         </tr>
                      </tbody>
                   </table>
                </div>
             </div>
          </div>
       </div>
    </div>
 </div>

@endsection