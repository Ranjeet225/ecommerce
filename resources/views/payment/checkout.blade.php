<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="description" content="GeniusCart-New - Multivendor Ecommerce system">
	<meta name="author" content="GeniusOcean">
	<title>{{$gs->title}}-checkout</title>
	<link rel="icon" type="image/x-icon" href="{{asset('assets/images/'.$gs->favicon)}}" />
	<!-- Google Font -->
	@if ($default_font->font_value)
	<link
		href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
		rel="stylesheet">
	@else
	<link href="https://fonts.googleapis.com/css2?family=Jost:wght@100;200;300;400;500;600;700;800;900&display=swap"
		rel="stylesheet">
	@endif
	<link rel="stylesheet"
		href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','', $gs->colors).'&header_color='.$gs->header_color) }}">
	<link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/front/css/toastr.min.css') }}">
	@if ($default_font->font_family)
	<link rel="stylesheet" id="colorr"
		href="{{ asset('assets/front/css/font.php?font_familly='.$default_font->font_family) }}">
	@else
	<link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='." Open Sans") }}">
	@endif
</head>

<body>

	<section class="checkout">
		<div class="container">
			<div class="row">


				<div class="col-lg-8 order-last order-lg-first">
					<form action="" method="POST" class="checkoutform">
						@include('includes.form-success')
						@include('includes.form-error')
						{{ csrf_field() }}
						<div class="checkout-area">
							<div class="order-box">
								<div class="content-box">
									<div class="content">
										<div class="payment-information">
											<h4 class="title">
												{{ __('Payment Info') }}
											</h4>
											<div class="row">
												<div class="col-lg-12">
													<div class="nav flex-column" role="tablist"
														aria-orientation="vertical">
														@foreach($gateways as $gt)
														@if($gt->type == 'manual')
														@if($order->dp == 0)
														<a class="nav-link payment " data-val=""
															data-show="{{$gt->showForm()}}"
															data-form="{{ $gt->showApiCheckoutLink() }}"
															data-href="{{ route('front.load.payment',['slug1' => $gt->showKeyword(),'slug2' => $gt->id]) }}"
															id="v-pills-tab{{ $gt->id }}-tab" data-toggle="pill"
															href="#v-pills-tab{{ $gt->id }}" role="tab"
															aria-controls="v-pills-tab{{ $gt->id }}"
															aria-selected="false">
															<div class="icon">
																<span class="radio"></span>
															</div>
															<p>
																{{ $gt->title }}
																@if($gt->subtitle != null)
																<small>
																	{{ $gt->subtitle }}
																</small>
																@endif
															</p>
														</a>
														@endif
														@else
														<a class="nav-link payment" data-val="{{ $gt->keyword }}"
															data-show="{{$gt->showForm()}}"
															data-form="{{ $gt->showApiCheckoutLink() }}"
															data-href="{{ route('front.load.payment',['slug1' => $gt->showKeyword(),'slug2' => $gt->id]) }}"
															id="v-pills-tab{{ $gt->id }}-tab" data-toggle="pill"
															href="#v-pills-tab{{ $gt->id }}" role="tab"
															aria-controls="v-pills-tab{{ $gt->id }}"
															aria-selected="false">
															<div class="icon">
																<span class="radio"></span>
															</div>
															<p>
																{{ $gt->name }}
																@if($gt->information != null)
																<small>
																	{{ $gt->getAutoDataText() }}
																</small>
																@endif
															</p>
														</a>
														@endif
														@endforeach

										
														
														{{-- wallet checkout start --}}
														<a class="nav-link payment" href="javascript:;" data-show="no"
															data-val="{{ $gt->keyword }}" data-toggle="pill" role="tab"
															data-form="{{ route('api.wallet.submit') }}"
															aria-controls="v-pills-tab{{ $gt->id }}"
															aria-selected="false">
															<div class="icon">
																<span class="radio"></span>
															</div>
															<p>
																{{ __('Wallet') }}
																@if($gt->information != null)
																<small>
																	{{ __('Pay from your wallet') }}
																</small>
																@endif

															</p>
														</a>
														{{-- wallet checkout end --}}
														

													</div>
												</div>
												<div class="col-lg-12">
													<div class="pay-area d-none">
														<div class="tab-content" id="v-pills-tabContent">
															@foreach($gateways as $gt)
															@if($gt->type == 'manual')
															@if($order->dp == 0)
															<div class="tab-pane fade" id="v-pills-tab{{ $gt->id }}"
																role="tabpanel"
																aria-labelledby="v-pills-tab{{ $gt->id }}-tab">
															</div>
															@endif
															@else
															<div class="tab-pane fade" id="v-pills-tab{{ $gt->id }}"
																role="tabpanel"
																aria-labelledby="v-pills-tab{{ $gt->id }}-tab">
															</div>
															@endif
															@endforeach
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-lg-12 mt-3">
												<div class="bottom-area">
													<input type="hidden"
														value="{{ $order->pay_amount * $order->currency_value }}"
														id="grandTotal">
													<button type="submit" id="final-btn"
														class="mybtn1">{{__('Checkout')}}</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<input type="hidden" id="preamount" value="{{ $order->pay_amount * $order->currency_value }}">
						<input type="hidden" name="order_number" value="{{ $order->order_number }}">
						<input type="hidden" name="email" value="{{ $order->customer_email }}">
						<input type="hidden" name="ref_id" id="ref_id" value="">
				</div>
				<div class="col-lg-4">
					<div class="right-area">
						<div class="order-box">
							<h4 class="title">{{ $langg->lang127 }}</h4>
							<div class="total-price">
								<p>
									@lang('Total Order Amount')
								</p>
								<p>
									@if($gs->currency_format == 0)
									<span id="total-cost">{{ $order->currency_sign }}<span class="total_price"> {{
											$order->pay_amount * $order->currency_value }}</span></span>
									@else
									<span id="total-cost"> <span class="total_price"> {{ $order->pay_amount *
											$order->currency_value }}</span>{{ $order->currency_sign }}</span>
									@endif
								</p>
							</div>

						</div>

					</div>
				</div>
				</form>
			</div>
		</div>
	</section>
	<script type="text/javascript">
		var mainurl = "{{url('/')}}";
		var gs      = {!! json_encode($gs) !!};
	</script>
	<!-- Include Scripts -->
	<script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
	<script src="https://js.paystack.co/v1/inline.js"></script>
	<script src="https://sdk.mercadopago.com/js/v2"></script>
	<script src="//voguepay.com/js/voguepay.js"></script>

	<script type="text/javascript">
		$('a.payment:first').addClass('active');
		$('.checkoutform').prop('action', $('a.payment:first').data('form'));
		$($('a.payment:first').attr('href')).load($('a.payment:first').data('href'));
		var show = $('a.payment:first').data('show');
		if (show != 'no') {
			$('.pay-area').removeClass('d-none');
		} else {
			$('.pay-area').addClass('d-none');
		}
		$($('a.payment:first').attr('href')).addClass('active').addClass('show');
	</script>
	@php
	$curr = App\Models\Currency::where('sign','=',$order->currency_sign)->firstOrFail();
	@endphp

	<script type="text/javascript">
		var ck = 0;
	
		$('.checkoutform').on('submit',function(e){
			if(ck == 0) {
				e.preventDefault();			
			$('#pills-step2-tab').removeClass('disabled');
			$('#pills-step2-tab').click();
	
		}else {
			$('#preloader').show();
		}
		$('#pills-step1-tab').addClass('active');
		});
	
		$('#step1-btn').on('click',function(){
			$('#pills-step1-tab').removeClass('active');
			$('#pills-step2-tab').removeClass('active');
			$('#pills-step3-tab').removeClass('active');
			$('#pills-step2-tab').addClass('disabled');
			$('#pills-step3-tab').addClass('disabled');
			$('#pills-step1-tab').click();
	
		});
	
	// Step 2 btn DONE
	
		$('#step2-btn').on('click',function(){
			$('#pills-step3-tab').removeClass('active');
			$('#pills-step1-tab').removeClass('active');
			$('#pills-step2-tab').removeClass('active');
			$('#pills-step3-tab').addClass('disabled');
			$('#pills-step2-tab').click();
			$('#pills-step1-tab').addClass('active');
	
		});
	
		$('#step3-btn').on('click',function(){
			 if($('a.payment:first').data('val') == 'paystack'){
				$('.checkoutform').prop('id','step1-form');
			}
			else if($('a.payment:first').data('val') == 'voguepay'){
				$('.checkoutform').prop('id','voguepay');
			}
			else {
				$('.checkoutform').prop('id','twocheckout');
			}
			$('#pills-step3-tab').removeClass('disabled');
			$('#pills-step3-tab').click();
	
			var shipping_user  = !$('input[name="shipping_name"]').val() ? $('input[name="name"]').val() : $('input[name="shipping_name"]').val();
			var shipping_location  = !$('input[name="shipping_address"]').val() ? $('input[name="address"]').val() : $('input[name="shipping_address"]').val();
			var shipping_phone = !$('input[name="shipping_phone"]').val() ? $('input[name="phone"]').val() : $('input[name="shipping_phone"]').val();
			var shipping_email= !$('input[name="shipping_email"]').val() ? $('input[name="email"]').val() : $('input[name="shipping_email"]').val();
	
			$('#shipping_user').html('<i class="fas fa-user"></i>'+shipping_user);
			$('#shipping_location').html('<i class="fas fas fa-map-marker-alt"></i>'+shipping_location);
			$('#shipping_phone').html('<i class="fas fa-phone"></i>'+shipping_phone);
			$('#shipping_email').html('<i class="fas fa-envelope"></i>'+shipping_email);
	
			$('#pills-step1-tab').addClass('active');
			$('#pills-step2-tab').addClass('active');
		});
	
		$('#final-btn').on('click',function(){
			ck = 1;
		})
	
	
	
	$('.payment').on('click',function(){
	
		if($(this).data('val') == 'paystack'){
			$('.checkoutform').attr('id','step1-form');
		}
		
		else if($(this).data('val') == 'mercadopago'){
			$('.checkoutform').attr('id','mercadopago');
			checkONE= 1;
		}
		else {
			$('.checkoutform').attr('id','');
		}
		$('.checkoutform').attr('action',$(this).attr('data-form'));
		$('.payment').removeClass('active');
		$(this).addClass('active');
		$('.pay-area #v-pills-tabContent .tab-pane.fade').not($(this).attr('href')).html('');
		var show = $(this).attr('data-show');
		if(show != 'no') {
			$('.pay-area').removeClass('d-none');
		}
		else {
			$('.pay-area').addClass('d-none');
		}
		$($('#v-pills-tabContent .tap-pane').removeClass('active show'));
		$($('#v-pills-tabContent #'+$(this).attr('aria-controls'))).addClass('active show').load($(this).attr('data-href'));
	})
		
		
		$(document).on('click','.shipping',function(){
			grandTotal();
		});
		
		$(document).on('click','.packing',function(){
			grandTotal();
		});
		
		let extra = 0;
		function grandTotal(){
			$('#grandTotal').val($('#preamount').val());
			let total = parseFloat($('#grandTotal').val());
			console.log(total);
			let shipping_charge = parseFloat($('.shipping:checked').attr('data'));
			let packing_charge = parseFloat($('.packing:checked').attr('data'));
			extra = shipping_charge + packing_charge;
			total += shipping_charge + packing_charge;
			
			$('.total_price').html(parseFloat(total).toFixed(2));
			$('#grandTotal').val(parseFloat(total).toFixed(2))
		}
	
	
			$(document).on('submit','#step1-form',function(){
				$('#preloader').hide();
				var val = $('#sub').val();
				var total = $('#grandTotal').val() ;
				
				total = Math.round(total);
					if(val == 0)
					{
					var handler = PaystackPop.setup({
					  key: '{{$paystackData['key']}}',
					  email: $('input[name=email]').val(),
					  amount: total * 100,
					  currency: "{{ $curr->name }}",
					  ref: ''+Math.floor((Math.random() * 1000000000) + 1),
					  callback: function(response){
						$('#ref_id').val(response.reference);
						$('#sub').val('1');
						$('#final-btn').click();
					  },
					  onClose: function(){
						  window.location.reload();
					  }
					});
					handler.openIframe();
						return false;                    
					}
					else {
						$('#preloader').show();
						return true;   
					}
			});
			
	
			closedFunction=function() {
				alert('window closed');
			}
	
			successFunction=function(transaction_id) {
				alert('Transaction was successful, Ref: '+transaction_id)
			}
	
			failedFunction=function(transaction_id) {
			 	alert('Transaction was not successful, Ref: '+transaction_id)
			}
	
	</script>