@extends('layouts.front')

@section('content')
@include('partials.global.common-header')

<!-- breadcrumb -->
<div class="full-row bg-light overlay-dark py-5"
    style="background-image: url({{ $gs->breadcrumb_banner ? asset('assets/images/'.$gs->breadcrumb_banner):asset('assets/images/noimage.png') }}); background-position: center center; background-size: cover;">
    <div class="container">
        <div class="row text-center text-white">
            <div class="col-12">
                <h3 class="mb-2 text-white">{{ __('Login Page') }}</h3>
            </div>
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 d-inline-flex bg-transparent p-0">
                        <li class="breadcrumb-item"><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>

                        <li class="breadcrumb-item active" aria-current="page">{{ __('Login') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- breadcrumb -->
<!--==================== Login Form Start ====================-->
<div class="full-row">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="woocommerce">
                    <div class="row">
                        <div class="col-lg-6 col-md-8 col-12 mx-auto">
                            <div class="sign-in-form border">
                                <h3>{{ __('Rider Login') }}</h3>

                                @include('alerts.admin.form-login')

                                @if(Session::has('auth-modal'))
                                <div class="alert alert-danger alert-dismissible">

                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    {{ Session::get('auth-modal') }}
                                </div>
                                @endif

                                @if(Session::has('forgot-modal'))
                                <div class="alert alert-success alert-dismissible">

                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    {{ Session::get('forgot-modal') }}
                                </div>
                                @endif
                                <form class="woocommerce-form-login" id="loginform"
                                    action="{{ route('rider.login.submit') }}" method="POST">
                                    @csrf
                                    <p>
                                        <label for="username">{{ __('Email address') }}<span
                                                class="required">*</span></label>
                                        <input type="email" class="form-control" name="email" id="username"
                                            placeholder="{{ __('Type Email Address') }}" required="">
                                    </p>
                                    <p>
                                        <label for="password">{{ __('Password') }}<span
                                                class="required">*</span></label>
                                        <input class="form-control" type="password" name="password" id="password"
                                            placeholder="{{ __('Type Password') }}" required="">
                                    </p>
                                    <div class="d-flex justify-content-between">
                                        <p>
                                            <a href="{{ route('rider.register') }}" class="text-secondary">{{ __("Don't have any account?") }}</a>
                                        </p>
                                        <p>
                                            <a href="{{ route('rider.forgot') }}" class="text-secondary">{{ __('Lost your password?') }}</a>
                                        </p>
                                    </div>

                                    <input type="hidden" name="modal" value="1">
                                    @if(Session::has('auth-modal'))
                                    <input type="hidden" name="auth_modal" value="1">
                                    @endif
                                    <input id="authdata" type="hidden" value="{{ __('Authenticating...') }}">

                                    <button type="submit"
                                        class="woocommerce-form-login__submit btn btn-primary border-0 rounded-0 submit-btn float-none w-100"
                                        name="login" value="Log in">{{ __('Log in') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--==================== Login Form Start ====================-->

@includeIf('partials.global.common-footer')
@endsection

@section('script')


@endsection