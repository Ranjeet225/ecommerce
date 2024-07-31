@extends('layouts.front')
@section('css')
 <link rel="stylesheet" href="{{ asset('assets/front/css/category/classic.css') }}"> 

<style>
    /* @media only screen and (max-width: 767px) {
        .banner-slide-item  {
        background-size: contain !important;
    }
    .banner-wrapper-item {
    min-height: 250px !important;
    padding: 0 15px !important;
    
}
    } */
</style>
@endsection
@section('content')
@include('partials.global.common-header')


@include('partials.global.subscription-popup')


@if($ps->slider == 1)
<div class="position-relative">
    <span class="nextBtn"></span>
    <span class="prevBtn"></span>
    <section class="home-slider owl-theme owl-carousel">
        @foreach($sliders as $data)
        <div class="banner-slide-item"
            style="background: url('{{asset('assets/images/sliders/'.$data->photo)}}') no-repeat center center / cover ;">
            <div class="container">
                <div class="banner-wrapper-item text-{{ $data->position }}">
                    <div class="banner-content ">
                        <h5 class="subtitle slide-h5" style="color: {{$data->subtitle_color}}">{{$data->subtitle_text}}
                        </h5>

                        <h2 class="title slide-h5" style="color: {{$data->title_color}}">{{$data->title_text}}</h2>

                        <p class="slide-h5" style="color: {{$data->details_color}}">{{$data->details_text}}</p>

                        <a href="{{$data->link}}" class="cmn--btn ">{{ __('SHOP NOW') }}</a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </section>
</div>
@endif




<!--==================== Category Section Start ====================-->
<!-- <div class="full-row pt-0 mt-5 px-sm-5 pb-0">
    <div class="container-fluid">
        <div
            class="row row-cols-xxl-6 row-cols-md-3 row-cols-sm-2 row-cols-2 g-3 coustom-categories-banner-1 e-wrapper-absolute e-hover-image-zoom">
            @foreach ($featured_categories as $fcategory)
            <div class="col">
                <div class="product type-product">
                    <div class="product-wrapper">
                        <div class="product-image">
                            <a href="{{route('front.category',$fcategory->slug)}}"><img
                                    src="{{asset('assets/images/categories/'.$fcategory->image)}}"
                                    alt="Product image"></a>
                        </div>
                        <div class="product-info">
                            <h6 class="product-title"><a
                                    href="{{route('front.category',$fcategory->slug)}}">{{$fcategory->name}}</a></h6>
                            <span class="strok">({{$fcategory->products_count}})</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div> -->
<!--==================== Category Section End ====================-->



@include('partials.theme.extraindex')

<!-- @if($ps->arrival_section == 1) -->
<!--==================== Best Month Offer Section Start ====================-->
<!-- <div class="full-row px-sm-5">
    <div class="container-fluid">
        <div class="row justify-content-center wow fadeInUp animated" data-wow-delay="200ms" data-wow-duration="1000ms">
            <div class="col-xxl-5 col-xl-7 col-lg-9">
                <div class="text-center mb-40">
                    <h2 class="text-center font-500 mb-4">{{__('Best Month Offer')}}</h2>
                    <span class="sub-title">{{__('Erat pellentesque curabitur euismod dui etiam pellentesque rhoncus
                        fermentum tristique lobortis lectus magnis. Consequat porta turpis maecenas')}}</span>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-xxl-6 col-md-12">
                <div class="banner-wrapper hover-img-zoom banner-one custom-class-122 bg-light">
                    <div class="banner-image overflow-hidden transation"><img
                            src="{{asset('assets/images/arrival/'.$arrivals[0]['photo'])}}" alt="Banner Image"></div>
                    <div class="banner-content y-center position-absolute">
                        <div class="middle-content">
                            <span class="up-to-sale">{{$arrivals[0]['up_sale']}}</span>
                            <h3><a href="{{$arrivals[0]['url']}}"
                                    class="text-dark text-decoration-none">{{$arrivals[0]['title']}}</a></h3>
                            <a href="{{$arrivals[0]['url']}}" class="category">{{$arrivals[0]['header']}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-md-6">
                <div class="banner-wrapper hover-img-zoom banner-one custom-class-123">
                    <div class="banner-image overflow-hidden transation"><img
                            src="{{asset('assets/images/arrival/'.$arrivals[1]['photo'])}}" alt="Banner Image"></div>
                    <div class="banner-content position-absolute">
                        <div class="middle-content">
                            <span class="up-to-sale">{{$arrivals[1]['up_sale']}}</span>
                            <h3><a href="{{$arrivals[1]['url']}}"
                                    class="text-dark text-decoration-none">{{$arrivals[1]['title']}}</a></h3>
                            <a href="{{$arrivals[1]['url']}}" class="category">{{$arrivals[1]['header']}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-md-6">
                <div class="banner-wrapper hover-img-zoom banner-one custom-class-124 bg-light">
                    <div class="banner-image overflow-hidden transation"><img
                            src="{{asset('assets/images/arrival/'.$arrivals[2]['photo'])}}" alt="Banner Image"></div>
                    <div class="banner-content position-absolute">
                        <span class="up-to-sale">{{$arrivals[2]['up_sale']}}</span>
                        <h3><a href="{{$arrivals[2]['url']}}"
                                class="text-dark text-decoration-none">{{$arrivals[2]['title']}}</a></h3>
                        <a href="{{$arrivals[2]['url']}}" class="category">{{$arrivals[2]['header']}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
<!--==================== Best Month Offer Section End ====================-->

<!-- @endif -->






@if(isset($visited))
@if($gs->is_cookie == 1)
<div class="cookie-bar-wrap show">
    <div class="container d-flex justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="row justify-content-center">
                <div class="cookie-bar">
                    <div class="cookie-bar-text">
                        {{ __('The website uses cookies to ensure you get the best experience on our website.') }}
                    </div>
                    <div class="cookie-bar-action">
                        <button class="btn btn-primary btn-accept">
                            {{ __('GOT IT!') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endif
<!-- Scroll to top -->
<a href="#" class="scroller text-white" id="scroll"><i class="fa fa-angle-up"></i></a>

@endsection
@section('script')
<script>
    var owl = $('.home-slider').owlCarousel({
        loop: true,
        nav: false,
        dots: true,
        items: 1,
        autoplay: true,
        margin: 0,
        animateIn: 'fadeInDown',
        animateOut: 'fadeOutUp',
        mouseDrag: false,
    })
    $('.nextBtn').click(function() {
        owl.trigger('next.owl.carousel', [300]);
    })
    $('.prevBtn').click(function() {
        owl.trigger('prev.owl.carousel', [300]);
    })
</script>
@endsection