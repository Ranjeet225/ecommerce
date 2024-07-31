@extends('layouts.front')


@section('content')


@include('partials.global.common-header')
<!-- breadcrumb -->
<div class="full-row bg-light overlay-dark py-5"
    style="background-image: url({{ $gs->breadcrumb_banner ? asset('assets/images/'.$gs->breadcrumb_banner):asset('assets/images/noimage.png') }}); background-position: center center; background-size: cover;">
    <div class="container">
        <div class="row text-center text-white">
            <div class="col-12">
                <h3 class="mb-2 text-white">{{ __('Service Area') }}</h3>
            </div>
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 d-inline-flex bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('rider-dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Service Area') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb -->
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
                        <div class="widget border-0 p-30 widget_categories bg-light account-info table-responsive">

                            <h4 class="widget-title down-line mb-30">{{ __('Service Area') }}</h4>
                            <div class="my-1">
                                <a href="{{route('rider-service-area-create')}}" class="mybtn1">@lang('Add Service
                                    Area')</a>
                            </div>


                            <table class="table order-table" cellspacing="0" id="example" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('Service Area') }}</th>
                                        <th>{{ __('Delivery Cost') }}</th>
                                        <th>{{ __('Auction') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($service_area as $area)
                                    <tr>
                                        <td data-label="{{ __('#Service Area') }}">
                                            {{$area->city->city_name}}
                                        </td>

                                        <td data-label="{{ __('Delivery Cost') }}">
                                            <p>
                                                {{ $curr->sign }}{{ round($area->price * $curr->value,2) }}
                                            </p>
                                        </td>

                                        <td data-label="{{ __('Auction') }}">
                                            <a class="mybtn1 sm1" href="{{route('rider-service-area-edit',$area->id)}}">
                                                {{ __('Edit') }}
                                            </a>
                                            <a class="mybtn1 sm1" href="{{route('rider-service-area-delete',$area->id)}}">
                                                {{ __('Delete') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No Orders Found.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

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
    $(document).ready(function() {
    $('.service_area').select2({
        placeholder: "Select Service Area",
        allowClear: true
    });


});
</script>
@endsection