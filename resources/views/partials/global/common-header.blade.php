<!--==================== Header Section Start ====================-->


<style>
   .pdng{
    padding: 8px 1px !important;
    }

    .text-general {
    padding: 0px 12px;
    color: #000000 !important;
}

.hedrspn{
    color:white !important;;
    text-decoration: none !important;
}


</style>
<div class="top-header d-none d-lg-block py-2 border-0 font-400"style="background-color: #bb8a8a;" >
        <div class="container-fluid">
            <div class="row align-items-center" >
                <div class="col-lg-4 sm-mx-none">
                    <a href="javascript:;" class="text-general">
                        <span class="hedrspn">{{__('Contact & Support')}} :
                            {{$ps->phone}}</span></a>
                </div>
                <div class="col-lg-8 d-flex">
                    <ul class="top-links d-flex ms-auto align-items-center">
                        @if (Auth::guard('web')->check() && Auth::guard('web')->user()->is_vendor == 2)
                        <li style="background-color: #fff;"><a class="border px-3 py-1" href="{{route('vendor.dashboard')}}">{{__('Vendor Panel')}}</a>
                        </li>
                        @else
                        <li style="background-color: #fff;"><a class="border px-3 py-1" href="{{route('vendor.register')}}">{{__('Sell')}}</a></li>
                        @endif

                        <!--@if (!Auth::guard('rider')->check())-->
                        <!--<li><a class="border px-3 py-1" href="{{route('rider.login')}}">{{__('Rider Login')}}</a></li>-->
                        <!--@endif -->

                        <li class="my-account-dropdown">
                            @php
                            $languges = App\Models\Language::all();
                            @endphp
                            <div class="language-selector nice-select">
                                <i class="fas fa-globe-americas text-dark"></i>
                                <select name="language" class="language selectors nice select2-js-init">
                                    @foreach($languges as $language)
                                    <option value="{{route('front.language',$language->id)}}" {{
                                        Session::has('language') ? ( Session::get('language')==$language->id ?
                                        'selected' : '' ) : ($languges->where('is_default','=',1)->first()->id ==
                                        $language->id ? 'selected' : '') }} >
                                        {{$language->language}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </li>
                        @php
                        $currencies = App\Models\Currency::all();
                        @endphp
                        <li class="my-account-dropdown">
                            <div class="currency-selector nice-select">
                                <span class="text-dark">
                                    {{ Session::has('currency') ?
                                    $currencies->where('id','=',Session::get('currency'))->first()->sign :
                                    DB::table('currencies')->where('is_default','=',1)->first()->sign }}
                                </span>
                                <select name="currency" class="currency selectors nice select2-js-init">
                                    @foreach($currencies as $currency)
                                    <option value="{{route('front.currency',$currency->id)}}" {{
                                        Session::has('currency') ? ( Session::get('currency')==$currency->id ?
                                        'selected' : '' ) : ($currencies->where('is_default','=',1)->first()->id ==
                                        $currency->id ? 'selected' : '') }}>
                                        {{$currency->name}}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </li>


                        <li class="my-account-dropdown">
                            <a href="my-account.html" class="has-dropdown hedrspn"><i
                                    class="flaticon-user-3 flat-mini me-1 hedrspn"></i></a>
                            <ul class="my-account-popup">
                                @if (Auth::guard('web')->check())
                                <li><a href="{{ route('user-dashboard') }}"><span class="menu-item-text">{{ ('User
                                            Panel') }}</span></a></li>
                                @if (Auth::guard('web')->user()->IsVendor())
                                <li><a href="{{ route('vendor.dashboard') }}"><span class="menu-item-text">{{ __('Vendor
                                            Panel') }}</span></a></li>
                                @endif
                                <li><a href="{{ route('user-profile') }}"><span class="menu-item-text">{{ __('Edit
                                            Profile') }}</span></a></li>
                                <li><a href="{{ route('user-logout') }}"><span class="menu-item-text">{{ __('Logout')
                                            }}</span></a></li>

                                @elseif(Auth::guard('rider')->check())
                                <li><a href="{{ route('rider-dashboard') }}"><span class="menu-item-text">{{ ('Rider
                                            Panel') }}</span></a></li>
                                <li><a href="{{ route('rider-profile') }}"><span class="menu-item-text">{{ __('Edit
                                            Profile') }}</span></a></li>
                                <li><a href="{{ route('rider-logout') }}"><span class="menu-item-text">{{ __('Logout')
                                            }}</span></a></li>

                                @else
                                <li><a href="{{ route('user.login') }}"><span class="menu-item-text sign-in">{{ __('User
                                            Login') }}</span></a></li>
                                <li><a href="{{ route('rider.login') }}"><span class="menu-item-text sign-in">{{
                                            __('Rider Login') }}</span></a></li>
                                <li><a href="{{ route('user.register') }}"><span class="menu-item-text join">{{
                                            __('Reg') }}</span></a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<header class="ecommerce-header px-lg-5">
    

    @php
    $categories = App\Models\Category::with('subs')->where('status',1)->get();
    $pages = App\Models\Page::get();
    @endphp
    <div class="main-nav py-4 d-none d-lg-block pdng">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-7 col-lg-9">
                    <nav class="navbar navbar-expand-lg nav-dark nav-primary-hover nav-line-active">
                        <a class="navbar-brand" href="{{ route('front.index') }}"><img class="nav-logo "
                                src="{{ asset('assets/images/'.$gs->logo) }}" alt="Image not found !"></a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <i class="flaticon-menu-2 flat-small text-primary"></i>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav ms-md-5">
                               
                                <li class="nav-item dropdown {{ request()->path() == '/' ? 'active':''}}">
                                    <a class="nav-link dropdown-toggle" href="{{ route('front.index') }}">{{ __('Home')
                                        }}</a>
                                </li>
                               
                                
                                 <li class="nav-item dropdown ">
                                    <a class="nav-link dropdown-toggle" href="{{ route('front.category') }}">{{ __('Shop') }}</a>
                                    <ul class="dropdown-menu">
                                    @foreach ($categories as $category)
                                    @if($category->subs->count() > 0)
                                                        @foreach ($category->subs as $subcategory)
                                                        <li><a class="dropdown-item"
                                                                href="{{route('front.category', [$category->slug, $subcategory->slug])}}{{!empty(request()->input('search')) ? '?search='.request()->input('search') : ''}}">{{$subcategory->name}}</a>
                                                        </li>

                                                @endforeach
                                                @endif
                                        @endforeach
                                    </ul>
                                </li>

                                <li class="nav-item dropdown ">
                                    <a class="nav-link dropdown-toggle" href="#">{{ __('Pages') }}</a>
                                    <ul class="dropdown-menu">
                                        @foreach($pages->where('header','=',1) as $data)
                                        <li><a class="dropdown-item" href="{{ route('front.vendor',$data->slug) }}">{{
                                                $data->title }}</a></li>
                                        @endforeach
                                    </ul>
                                </li>

                                
                                
                                @if ($ps->testimonial == 1)
                                <li class="nav-item dropdown {{ request()->path()=='testimonial' ? 'active' : '' }}">
                                    <a class="nav-link dropdown-toggle" href="{{ route('front.testimonial') }}">{{ __('Testimonial')
                                        }}</a>
                                </li>
                                @endif
                                @if ($ps->faq == 1)
                                <li class="nav-item dropdown {{ request()->path()=='faq' ? 'active' : '' }}">
                                    <a class="nav-link dropdown-toggle" href="{{ route('front.faq') }}">{{ __('FAQ')
                                        }}</a>
                                </li>
                                @endif
                                @if ($ps->contact == 1)
                                <li class="nav-item {{ request()->path()=='contact' ? 'active' : '' }}"><a
                                        class="nav-link" href="{{ route('front.contact') }}">{{ __('Contact') }}</a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </nav>
                </div>
                <div class="col-xl-5 col-lg-3">
                    <div class="margin-right-1 d-flex align-items-center justify-content-end h-100">
                        <div class="product-search-one flex-grow-1 global-search touch-screen-view">
                            <form id="searchForm" class="search-form form-inline search-pill-shape"
                                action="{{ route('front.category', [Request::route('category'),Request::route('subcategory'),Request::route('childcategory')]) }}"
                                method="GET">

                                @if (!empty(request()->input('sort')))
                                <input type="hidden" name="sort" value="{{ request()->input('sort') }}">
                                @endif
                                @if (!empty(request()->input('minprice')))
                                <input type="hidden" name="minprice" value="{{ request()->input('minprice') }}">
                                @endif
                                @if (!empty(request()->input('maxprice')))
                                <input type="hidden" name="maxprice" value="{{ request()->input('maxprice') }}">
                                @endif
                                <input type="text" id="prod_name" class="col form-control search-field " name="search"
                                    placeholder="Search Product For" value="{{ request()->input('search') }}">
                                <div class=" categori-container select-appearance-none " id="catSelectForm">
                                    <select name="category" class="form-control categoris select2-js-search-init">
                                        <option selected="">{{ __('All Categories') }}</option>
                                        @foreach($categories->where('status',1) as $data)
                                        <option value="{{ $data->slug }}" {{ Request::route('category')==$data->slug ?
                                            'selected' : '' }}>
                                            {{ $data->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" name="submit" class="search-submit"><i
                                        class="flaticon-search flat-mini text-white"></i></button>

                            </form>
                        </div>
                        <div class="autocomplete">
                            <div id="myInputautocomplete-list" class="autocomplete-items"></div>
                        </div>

                        <div class="search-view d-xxl-none">
                            <a href="#" class="search-pop top-quantity d-flex align-items-center text-decoration-none">
                                <i class="flaticon-search flat-mini text-dark mx-auto"></i>
                            </a>
                        </div>
                        <div class="header-cart-1">
                            @if (Auth::guard('web')->check())
                            <a href="{{ route('user-wishlists') }}" class="cart " title="View Wishlist">
                                <div class="cart-icon"><i class="flaticon-like flat-mini mx-auto text-dark"></i> <span
                                        class="header-cart-count " id="wishlist-count">{{
                                        Auth::guard('web')->user()->wishlistCount()
                                        }}</span></div>
                            </a>
                            @else
                            <a href="{{ route('user.login') }}" class="cart " title="View Wishlist">
                                <div class="cart-icon"><i class="flaticon-like flat-mini mx-auto text-dark"></i> <span
                                        class="header-cart-count" id="wishlist-count">{{ 0 }}</span></div>
                            </a>
                            @endif
                        </div>

                        <div class="header-cart-1">
                            <a href="{{ route('product.compare') }}" class="cart " title="Compare">
                                <div class="cart-icon"><i class="flaticon-shuffle flat-mini mx-auto text-dark"></i>
                                    <span class="header-cart-count " id="compare-count">{{ Session::has('compare') ?
                                        count(Session::get('compare')->items) : '0' }}</span>
                                </div>
                            </a>
                        </div>

                        <div class="header-cart-1">
                            <a href="{{ route('front.cart') }}" class="cart has-cart-data" title="View Cart">
                                <div class="cart-icon"><i class="flaticon-shopping-cart flat-mini"></i> <span
                                        class="header-cart-count" id="cart-count1">{{ Session::has('cart') ?
                                        count(Session::get('cart')->items) : '0' }}</span></div>
                                <div class="cart-wrap">
                                    <div class="cart-text">@lang('Cart')</div>
                                    <span class="header-cart-count">{{ Session::has('cart') ?
                                        count(Session::get('cart')->items) : '0' }}</span>
                                </div>
                            </a>
                            @include('load.cart')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-sticky bg-white py-10">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xxl-2 col-xl-2 col-lg-3 col-6 order-lg-1">
                    <div class="d-flex align-items-center h-100 md-py-10">
                        <div class="nav-leftpush-overlay">
                            <nav class="navbar navbar-expand-lg nav-general nav-primary-hover">
                                <button type="button" class="push-nav-toggle d-lg-none border-0">
                                    <i class="flaticon-menu-2 flat-small text-primary"></i>
                                </button>
                                <div class="navbar-slide-push transation-this">
                                    <div
                                        class="login-signup bg-secondary d-flex justify-content-between py-10 px-20 align-items-center">
                                        <a href="{{route('user.login')}}" class="d-flex align-items-center text-white">
                                            <i class="flaticon-user flat-small me-1"></i>
                                            <span>Login/Signup</span>
                                        </a>
                                        <span class="slide-nav-close"><i
                                                class="flaticon-cancel flat-mini text-white"></i></span>
                                    </div>
                                    <div class="menu-and-category">
                                        <ul class="nav nav-pills wc-tabs" id="menu-and-category" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active" id="pills-push-menu-tab"
                                                    data-bs-toggle="pill" href="#pills-push-menu" role="tab"
                                                    aria-controls="pills-push-menu" aria-selected="true">Menu</a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link" id="pills-push-categories-tab" data-bs-toggle="pill"
                                                    href="#pills-push-categories" role="tab"
                                                    aria-controls="pills-push-categories"
                                                    aria-selected="true">Categories</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="menu-and-categoryContent">
                                            <div class="tab-pane fade show active woocommerce-Tabs-panel woocommerce-Tabs-panel--description"
                                                id="pills-push-menu" role="tabpanel"
                                                aria-labelledby="pills-push-menu-tab">
                                                <div class="push-navbar">
                                                    <ul class="navbar-nav">
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="{{ route('front.index') }}">{{
                                                                __('Home') }}</a>
                                                        </li>
                                                        <li class="nav-item ">
                                                            <a class="nav-link" href="{{ route('front.category') }}">{{
                                                                __('PRODUCT') }}</a>
                                                        </li>
                                                        <li class="nav-item dropdown">
                                                            <a class="nav-link dropdown-toggle" href="#">{{ __('Pages')
                                                                }}</a>
                                                            <ul class="dropdown-menu">
                                                                @foreach($pages->where('header','=',1) as $data)
                                                                <li><a class="dropdown-item"
                                                                        href="{{ route('front.vendor',$data->slug) }}">{{
                                                                        $data->title }}</a></li>
                                                                @endforeach
                                                            </ul>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" href="{{ route('front.blog') }}">{{
                                                                __('Blog') }}</a>
                                                        </li>

                                                        <li class="nav-item">
                                                            <a class="nav-link" href="{{ route('front.faq') }}">{{
                                                                __('FAQ') }}</a>
                                                        </li>
                                                        <li class="nav-item"><a class="nav-link"
                                                                href="{{ route('front.contact') }}">{{ __('Contact')
                                                                }}</a></li>
                                                    </ul>

                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="pills-push-categories" role="tabpanel"
                                                aria-labelledby="pills-push-categories-tab">
                                                <div class="categories-menu">
                                                    <ul class="menu">
                                                        @foreach ($categories as $category)
                                                        <li class="menu-item-has-children"><a
                                                                href="{{route('front.category',$category->slug)}}">{{$category->name}}</a>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                        <a class="navbar-brand" href="{{route('front.index')}}"><img class="nav-logo"
                                src="{{asset('assets/images/'.$gs->logo)}}" alt="Image not found !"></a>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-3 col-6 order-lg-3">
                    <div class="margin-right-1 d-flex align-items-center justify-content-end h-100 md-py-10">
                        <div class="sign-in position-relative font-general my-account-dropdown">
                            <a href="my-account.html"
                                class="has-dropdown d-flex align-items-center text-dark text-decoration-none"
                                title="My Account">
                                <i class="flaticon-user-3 flat-mini me-1 mx-auto" ></i>
                            </a>
                            <ul class="my-account-popup">
                                @if (Auth::guard('web')->check())
                                <li><a href="{{ route('user-dashboard') }}"><span class="menu-item-text">{{ ('User
                                            Panel') }}</span></a></li>
                                @if (Auth::guard('web')->user()->IsVendor())
                                <li><a href="{{ route('vendor.dashboard') }}"><span class="menu-item-text">{{ __('Vendor
                                            Panel') }}</span></a></li>
                                @endif
                                <li><a href="{{ route('user-profile') }}"><span class="menu-item-text">{{ __('Edit
                                            Profile') }}</span></a></li>
                                <li><a href="{{ route('user-logout') }}"><span class="menu-item-text">{{ __('Logout')
                                            }}</span></a></li>

                                @elseif(Auth::guard('rider')->check())
                                <li><a href="{{ route('rider-dashboard') }}"><span class="menu-item-text">{{ ('User
                                            Panel') }}</span></a></li>
                                <li><a href="{{ route('rider-profile') }}"><span class="menu-item-text">{{ __('Edit
                                            Profile') }}</span></a></li>
                                <li><a href="{{ route('rider-logout') }}"><span class="menu-item-text">{{ __('Logout')
                                            }}</span></a></li>
                                @else
                                <li><a href="{{ route('user.login') }}"><span class="menu-item-text sign-in">{{ __('User
                                            Login') }}</span></a></li>
                                <li><a href="{{ route('rider.login') }}"><span class="menu-item-text sign-in">{{
                                            __('Rider Login') }}</span></a></li>
                                <li><a href="{{ route('user.register') }}"><span class="menu-item-text join">{{
                                            __('Reg') }}</span></a></li>
                                @endif
                            </ul>
                        </div>


                        @if (Auth::check())
                        <div class="wishlist-view">
                            <a href="{{route('user-wishlists')}}"
                                class="position-relative top-quantity d-flex align-items-center text-white text-decoration-none">
                                <i class="flaticon-like flat-mini text-dark mx-auto"></i>
                            </a>
                        </div>
                        @else
                        <div class="wishlist-view">
                            <a href="{{route('user.login')}}"
                                class="position-relative top-quantity d-flex align-items-center text-white text-decoration-none">
                                <i class="flaticon-like flat-mini text-dark mx-auto"></i>
                            </a>
                        </div>
                        @endif

                        <div class="refresh-view">
                            <a href="{{ route('product.compare') }}"
                                class="position-relative top-quantity d-flex align-items-center text-dark text-decoration-none">
                                <i class="flaticon-shuffle flat-mini text-dark mx-auto"></i>
                            </a>
                        </div>
                        <div class="header-cart-1">
                            <a href="{{ route('front.cart') }}" class="cart has-cart-data" title="View Cart">
                                <div class="cart-icon"><i class="flaticon-shopping-cart flat-mini"></i> <span
                                        class="header-cart-count" id="cart-count">{{ Session::has('cart') ?
                                        count(Session::get('cart')->items) : '0' }}</span></div>
                                <div class="cart-wrap">
                                    <div class="cart-text">Cart</div>
                                    <span class="header-cart-count">{{ Session::has('cart') ?
                                        count(Session::get('cart')->items) : '0' }}</span>
                                </div>
                            </a>
                            @include('load.cart')
                        </div>
                    </div>
                </div>
                <div class="col-xxl-7 col-xl-6 col-lg-6 col-12 order-lg-2">
                    <div class="product-search-one">

                        <form id="searchForm" class="search-form form-inline search-pill-shape"
                            action="{{ route('front.category', [Request::route('category'),Request::route('subcategory'),Request::route('childcategory')]) }}"
                            method="GET">

                            @if (!empty(request()->input('sort')))
                            <input type="hidden" name="sort" value="{{ request()->input('sort') }}">
                            @endif
                            @if (!empty(request()->input('minprice')))
                            <input type="hidden" name="minprice" value="{{ request()->input('minprice') }}">
                            @endif
                            @if (!empty(request()->input('maxprice')))
                            <input type="hidden" name="maxprice" value="{{ request()->input('maxprice') }}">
                            @endif
                            <input type="text" id="prod_name" class="col form-control search-field " name="search"
                                placeholder="Search Product For" value="{{ request()->input('search') }}">
                            <div class=" categori-container select-appearance-none " id="catSelectForm">
                                <select name="category" class="form-control categoris select2-js-search-init">
                                    <option selected="">{{ __('All Categories') }}</option>
                                    @foreach($categories->where('status',1) as $data)
                                    <option value="{{ $data->slug }}" {{ Request::route('category')==$data->slug ?
                                        'selected' : '' }}>
                                        {{ $data->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" name="submit" class="search-submit"><i
                                    class="flaticon-search flat-mini text-white"></i></button>

                        </form>



                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!--==================== Header Section End ====================-->