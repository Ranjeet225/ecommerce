<div class="dashboard-overlay">&nbsp;</div>
<div id="sidebar" class="sidebar-blog bg-light p-30">
  <div class="dashbaord-sidebar-close d-xl-none">
    <i class="fas fa-times"></i>
  </div>
    <div class="widget border-0 py-0 widget_categories">
        <h4 class="widget-title down-line">{{ __('Dashboard') }}</h4>
        <ul>
            <li class=""><a class="{{ Request::url() == route('rider-dashboard') ? 'active':'' }}" href="{{ route('rider-dashboard') }}">@lang('Dashboard')</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-orders') ? 'active':'' }}" href="{{ route('rider-orders') }}">{{ __('Pending Order') }}</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-orders').'?type=complete' ? 'active':'' }}" href="{{ route('rider-orders').'?type=complete' }}">{{ __('Complete Order') }}</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-service-area') ? 'active':'' }}" href="{{ route('rider-service-area') }}">{{ __('Service Area') }}</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-profile') ? 'active':'' }}" href="{{ route('rider-profile') }}">{{ __('Edit Profile') }}</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-wwt-index') ? 'active':'' }}" href="{{route('rider-wwt-index')}}">{{ __('Withdraw') }}</a></li>
            <li class=""><a class="{{ Request::url() == route('rider-reset') ? 'active':'' }}" href="{{ route('rider-reset') }}">{{ __('Reset Password') }}</a></li>
            <li class=""><a class="" href="{{ route('rider-logout') }}">{{ __('Logout') }}</a></li>
          </ul>
    </div>
</div>
  
