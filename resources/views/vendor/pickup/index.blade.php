@extends('layouts.vendor')

@section('content')
<input type="hidden" id="headerdata" value="{{ __('PICKUP POINT') }}">
<div class="content-area">
  <div class="mr-breadcrumb">
    <div class="row">
      <div class="col-lg-12">
        <h4 class="heading">{{ __('Pickup Point') }}</h4>
        <ul class="links">
          <li>
            <a href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a>
          </li>

          <li>
            <a href="javascript:;">{{ __('Settings') }}</a>
          </li>

          <li>
            <a href="{{ route('vendor-pickup-point-index') }}">{{ __('Pickup Point') }}</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="product-area">
    <div class="row">
      <div class="col-lg-12">
        <div class="mr-table allproduct">

          @include('alerts.admin.form-success')

          <div class="table-responsive">
            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th>{{ __('Location') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th>{{ __('Options') }}</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ADD / EDIT MODAL --}}

<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">


  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="submit-loader">
        <img src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
      </div>
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- ADD / EDIT MODAL ENDS --}}


{{-- DELETE MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header d-block text-center">
        <h4 class="modal-title d-inline-block">{{ __('Confirm Delete') }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>


      <div class="modal-body">
        <p class="text-center">{{ __('You are about to delete this Pickup Point.') }}</p>
        <p class="text-center">{{ __('Do you want to proceed?') }}</p>
      </div>


      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
        <form action="" class="d-inline delete-form" method="POST">
          <input type="hidden" name="_method" value="delete" />
          <input type="hidden" name="_token" value="{{ csrf_token() }}">
          <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- DELETE MODAL ENDS --}}

@endsection

@section('scripts')


{{-- DATA TABLE --}}

<script type="text/javascript">
  (function($) {
		"use strict";

    var table = $('#geniustable').DataTable({
         ordering: false,
               processing: true,
               serverSide: true,
               ajax: '{{ route('vendor-pickup-point-datatables') }}',
               columns: [
                  { data: 'location', name: 'location' },
                  { data: 'status', name: 'status' },
                  { data: 'action', searchable: false, orderable: false }

                     ],
                language : {
                  processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                },
                drawCallback : function( settings ) {
	    				    $('.select').niceSelect();
				        }
            });

        $(function() {
        $(".btn-area").append('<div class="col-sm-4 table-contents">'+
          '<a class="add-btn" data-href="{{route('vendor-pickup-point-create')}}" id="add-data" data-toggle="modal" data-target="#modal1">'+
          '<i class="fas fa-plus"></i><span class="remove-mobile">{{ __("Add New") }}<span>'+
          '</a>'+
          '</div>');
      });

{{-- DATA TABLE ENDS--}}

})(jQuery);

</script>

@endsection