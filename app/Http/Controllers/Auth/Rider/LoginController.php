<?php

namespace App\Http\Controllers\Auth\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;

class LoginController extends Controller
{
  public function __construct()
  {
    $this->middleware('guest', ['except' => ['logout', 'userLogout']]);
  }

  public function login(Request $request)
  {
    //--- Validation Section
    $rules = [
      'email'   => 'required|email',
      'password' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
    }
    //--- Validation Section Ends

    // Attempt to log the user in
    if (Auth::guard('rider')->attempt(['email' => $request->email, 'password' => $request->password])) {
      // if successful, then redirect to their intended location

      // Check If Email is verified or not
      if (Auth::guard('rider')->user()->email_verified == 'No') {
        Auth::guard('rider')->logout();
        return response()->json(array('errors' => [0 => __('Your Email is not Verified!')]));
      }

      if (Auth::guard('rider')->user()->ban == 1) {
        Auth::guard('rider')->logout();
        return response()->json(array('errors' => [0 => __('Your Account Has Been Banned.')]));
      }

      // Login as User
      return response()->json(redirect()->intended(route('rider-dashboard'))->getTargetUrl());
    }

    // if unsuccessful, then redirect back to the login with the form data
    return response()->json(array('errors' => [0 => __('Credentials Doesn\'t Match !')]));
  }

  public function logout()
  {
    Auth::guard('rider')->logout();
    return redirect('/');
  }
}
