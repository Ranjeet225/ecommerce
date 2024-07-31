@extends('layouts.front')

@section('css')
<style>
    .service_area .select2-container {
        display: unset !important;
    }
</style>
@endsection
@section('content')


@include('partials.global.common-header')


<!-- breadcrumb -->
<div class="full-row bg-light overlay-dark py-5"
    style="background-image: url({{ $gs->breadcrumb_banner ? asset('assets/images/'.$gs->breadcrumb_banner):asset('assets/images/noimage.png') }}); background-position: center center; background-size: cover;">
    <div class="container">
        <div class="row text-center text-white">
            <div class="col-12">
                <h3 class="mb-2 text-white">{{ __('Add Service Area') }}</h3>
            </div>
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 d-inline-flex bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('rider-dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Add Service Area') }}</li>
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
                        <div class="widget border-0 p-40 widget_categories bg-light account-info">
                            <h4 class="widget-title down-line mb-30">{{ __('Add Service Area') }}
                            </h4>
                            <div class="edit-info-area">
                                <div class="body">
                                    <div class="edit-info-area-form">
                                        <div class="gocover"
                                            style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
                                        </div>
                                        <form id="userform" action="{{route('rider-service-area-store')}}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="row mb-4">
                                                <div class="col-lg-12 mb-3">
                                                    <label for="service_area_id">@lang('Service Area')</label>
                                                    <select class="service_area input-field form-control"
                                                        name="service_area_id" id="service_area_id">
                                                        @foreach ($cities as $city)
                                                        <option value="{{$city->id}}">{{$city->city_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-12">
                                                    <label for="">@lang('Delivery Cost') ({{$curr->name}})</label>
                                                    <input name="price" step="any" type="number"
                                                        class="input-field form-control border"
                                                        placeholder="{{ __('Delivery Cost') }}" required="" value="">
                                                </div>
                                            </div>

                                            <div class="form-links">
                                                <button class="submit-btn btn btn-primary" type="submit">{{ __('Save')
                                                    }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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