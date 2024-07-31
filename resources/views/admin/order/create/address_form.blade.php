@if (Session::has('order_address'))
@php
    $user = Session::get('order_address');
@endphp
<div class="row mt-2">
  <div class="col col-md-4 col-sm-6">
    <label for="name">Name *</label>
    <input type="text" class="form-control" required name="customer_name" id="name" value="{{$user['customer_name']}}" placeholder="Name">
  </div>
  <div class="col col-md-4 col-sm-6">
    <label for="email">Email *</label>
    <input type="text" class="form-control" required name="customer_email" id="email" placeholder="Email" value="{{$user['customer_email']}}">
  </div>
  <div class="col col-md-4 col-sm-6">
    <label for="phone">Phone *</label>
    <input type="text" class="form-control" required name="customer_phone" id="phone" placeholder="Phone" value="{{$user['customer_phone']}}">
  </div>
  <div class="col col-md-12 col-sm-12">
    <label for="customer_address">Address *</label>
    <input type="text" class="form-control" required name="customer_address" id="customer_address" placeholder="Address" value="{{$user['customer_address']}}">
  </div>
</div>
<div class="row">
  <div class="col col-md-6 col-sm-6">
    <label for="customer_country">Country * </label>
    <select type="text" class="form-control" name="customer_country" id="customer_country" required>
      <option value="">{{ __('Select Country') }}</option>
      @foreach (DB::table('countries')->get() as $data)
          <option value="{{ $data->country_name }}"  {{$user['customer_country'] == $data->country_name ? 'selected' : ''}}>
              {{ $data->country_name }}
          </option>		
       @endforeach
  </select>
  </div>
  <div class="col col-md-6 col-sm-6">
    <label for="customer_city">City</label>
    <input type="text" class="form-control" name="customer_city" id="customer_city" placeholder="City" value="{{$user['customer_city']}}">
  </div>
  <div class="col col-md-6 col-sm-6">
    <label for="customer_state">State</label>
    <input type="text" class="form-control" name="customer_state" id="customer_state" placeholder="State" value="{{$user['customer_state']}}">
  </div>
 
  <div class="col col-md-6 col-sm-6">
    <label for="post_code">Postal Code</label>
    <input type="text" class="form-control" name="customer_zip" id="post_code" placeholder="Postal Code" value="{{$user['customer_zip']}}">
  </div>

</div>

@else  




@php
    $isUser = isset($isUser) ? $isUser : false;
   
@endphp
@if ($isUser == 1)
  <div class="row mt-2">
    <div class="col col-md-4 col-sm-6">
      <label for="name">Name *</label>
      <input type="text" class="form-control" required name="customer_name" id="name" value="{{$user['name']}}" placeholder="Name">
    </div>
    <div class="col col-md-4 col-sm-6">
      <label for="email">Email *</label>
      <input type="text" class="form-control" required name="customer_email" id="email" placeholder="Email" value="{{$user['email']}}">
    </div>
    <div class="col col-md-4 col-sm-6">
      <label for="phone">Phone *</label>
      <input type="text" class="form-control" required name="customer_phone" id="phone" placeholder="Phone" value="{{$user['phone']}}">
    </div>
    <div class="col col-md-12 col-sm-12">
      <label for="customer_address">Address *</label>
      <input type="text" class="form-control" required name="customer_address" id="customer_address" placeholder="Address" value="{{$user['address']}}">
    </div>
  </div>
  <div class="row">
    <div class="col col-md-6 col-sm-6">
      <label for="customer_country">Country * </label>
      <select type="text" class="form-control" name="customer_country" id="customer_country" required>
        <option value="">{{ __('Select Country') }}</option>
        @foreach (DB::table('countries')->get() as $data)
            <option value="{{ $data->country_name }}"  {{$user['country'] == $data->country_name ? 'selected' : ''}}>
                {{ $data->country_name }}
            </option>		
         @endforeach
    </select>
    </div>
    <div class="col col-md-6 col-sm-6">
      <label for="customer_city">City</label>
      <input type="text" class="form-control" name="customer_city" id="customer_city" placeholder="City" value="{{$user['city']}}">
    </div>
    <div class="col col-md-6 col-sm-6">
      <label for="customer_state">State</label>
      <input type="text" class="form-control" name="customer_state" id="customer_state" placeholder="State" value="{{$user['state']}}">
    </div>
   
    <div class="col col-md-6 col-sm-6">
      <label for="post_code">Postal Code</label>
      <input type="text" class="form-control" name="customer_zip" id="post_code" placeholder="Postal Code" value="{{$user['zip']}}">
    </div>
 
  </div>
@else

  <div class="row mt-2">
    <div class="col col-md-4 col-sm-6">
      <label for="name">Name *</label>
      <input type="text" class="form-control" required name="customer_name" id="name" placeholder="Name">
    </div>
    <div class="col col-md-4 col-sm-6">
      <label for="email">Email *</label>
      <input type="text" class="form-control" required name="customer_email" id="email" placeholder="Email">
    </div>
    <div class="col col-md-4 col-sm-6">
      <label for="phone">Phone *</label>
      <input type="text" class="form-control" required name="customer_phone" id="phone" placeholder="Email">
    </div>
    <div class="col col-md-12 col-sm-12">
      <label for="customer_address">Address *</label>
      <input type="text" class="form-control" required name="customer_address" id="customer_address" placeholder="Address">
    </div>
  </div>
  <div class="row">
    <div class="col col-md-6 col-sm-6">
      <label for="customer_country">Country * </label>
      <select  class="form-control" name="customer_country" id="customer_country" required>
        <option value="">{{ __('Select Country') }}</option>
        @foreach (DB::table('countries')->get() as $data)
            <option value="{{ $data->country_name }}" >
                {{ $data->country_name }}
            </option>		
         @endforeach
    </select>
    </div>
    <div class="col col-md-6 col-sm-6">
      <label for="customer_city">City</label>
      <input type="text" class="form-control" name="customer_city" id="customer_city" placeholder="City">
    </div>
    <div class="col col-md-6 col-sm-6">
      <label for="customer_state">State</label>
      <input type="text" class="form-control" name="customer_state" id="customer_state" placeholder="State">
    </div>
   
    <div class="col col-md-6 col-sm-6">
      <label for="post_code">Postal Code</label>
      <input type="text" class="form-control" name="customer_zip" id="post_code" placeholder="Postal Code">
    </div>
  </div>
@endif
    
@endif
