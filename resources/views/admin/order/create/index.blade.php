@extends('layouts.admin') 
@section('styles')
<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection
@section('content')  
<div class="content-area">
<div class="mr-breadcrumb">
   <div class="row">
      <div class="col-lg-12">
         <h4 class="heading">{{ __('Create An Order') }}</h4>
         <ul class="links">
            <li>
               <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
            </li>
            <li>
               <a href="javascript:;">{{ __('Orders') }} </a>
            </li>
            <li>
               <a href="{{ route('admin-order-create') }}">{{ __('Create An Order') }}</a>
            </li>
         </ul>
      </div>
   </div>
</div>

<div class="add-product-content1 add-product-content2">
    <div class="row">
      <div class="col-lg-12">
        <div class="product-description">
          <form action="{{route('admin.order.create.view')}}" method="POST">
            @csrf
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            
            @include('alerts.admin.form-both') 
          
           
              <div class="product-area">
                <div class="row">
                  <div class="col-lg-3 border-right p-0">
                    <div class="mr-table allproduct">
                      @include('alerts.admin.form-success') 
                      <div class="table-responsive" style="height: 100vh">
                          <table id="order-geniustable"   class="table table-hover dt-responsive" cellspacing="0" width="100%">
                            <thead>
                              <tr>
                                <th >{{ __('Product') }}</th>
                                <th>{{ __('Options') }}</th>
                              </tr>
                            </thead>
                          </table>
                      </div>
                    </div>
                  </div>
                 
                  <div class="col-lg-9" >

                    <div class="py-4 px-4 my-2 mx-4 border">
                      <div class="text-center">
                        <h3>User Details</h3>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-lg-4">
                            <label for="phone_number">Select User</label>
                            <div class="input-group mb-2">
                              <select name="user_id" class="form-control order_create_user" id="order_create_user">
                                <option value="">Select User</option>
                                @foreach(App\Models\User::where('status','!=',2)->get() as $user)
                                    <option value="{{$user->id}}" {{Session::has('order_address') && Session::get('order_address')['user_id'] == $user->id ? 'selected' : ''}} >{{$user->name}} (Phone : {{$user->phone}})</option>
                                @endforeach
                            </select>
                            </div>
                           
                        </div>
                      </div>
                     
                      <div id="order_create_user_address">
                        @include('admin.order.create.address_form')
                      </div>

                    </div>
                    <div id="view_table_order">
                      @include('admin.order.create.product_add_table')
                    </div>
                  </div>
                </div>
              </div>
            </form>
        </div>
      </div>
    </div>
  </div>

</div>


  @include('admin.order.create.adds-product')


@endsection    


@section('scripts')

{{-- DATA TABLE --}}

  <script type="text/javascript">

 
  (function($) {
		"use strict";
    $('#sidebarCollapse').click();
		var table = $('#order-geniustable').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        sorting: false,
        paging: false,

        ajax: '{{ route('admin-order-product-datatables') }}',
        columns: [
                { data: 'name', name: 'name' },
                { data: 'action', searchable: false, orderable: false }
              ],
        language : {
          processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }
      });

	})(jQuery);


  
$(document).on('click','.order_product_add',function(e){

if(admin_loader == 1)
{
  $('.submit-loader').show();
}

$('#product-show').html('').load(mainurl+"/admin/order/create/product-show/"+$(this).attr('data-href'),function(response, status, xhr){
            if(status == "success")
            {
                if(admin_loader == 1)
                {
                    $('.submit-loader').hide();
                }
            }
        });
});



$(document).on('change','#order_create_user',function(){
  let user_id = $(this).val();
  if(user_id != ''){
    $.ajax({
      url: mainurl+'/admin/order/create/user-address',
      type: 'GET',
      data: {
        user_id: user_id
      },
      success: function(data){
        $('#order_create_user_address').html(data);
      }
    });
  }else{
    $('#order_create_user_address').find('input').val('');
  }
})


$(document).on('click','.removeOrder',function(){
  let status = confirm('Are you sure to remove this product?');
  if(status){
    let removeUrl = $(this).attr('data-href');
    $.ajax({
      url: removeUrl,
      type: 'GET',
      success: function(data){
        $('#view_table_order').html(data);
      }
    });
    
  }else{
    return false;
  }
})

$(document).ready(function(){
  $('.order_create_user').select2();
});
</script>

{{-- DATA TABLE --}}





@endsection   