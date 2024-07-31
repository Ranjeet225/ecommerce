<?php

namespace App\Http\Controllers\Rider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Generalsetting;
use App\Models\User;
use App\Classes\GeniusMailer;
use App\Http\Controllers\Front\FrontBaseController;
use App\Models\Notification;
use Auth;

use Validator;

class RegisterController extends FrontBaseController
{

	public function showRegisterForm()
	{

		return view('rider.register');
	}



	public function token($token)
	{
		$gs = Generalsetting::findOrFail(1);

		if ($gs->is_verification_email == 1) {
			$user = User::where('verification_link', '=', $token)->first();
			if (isset($user)) {
				$user->email_verified = 'Yes';
				$user->update();
				$notification = new Notification;
				$notification->user_id = $user->id;
				$notification->save();
				Auth::guard('web')->login($user);
				return redirect()->route('user-dashboard')->with('success', 'Email Verified Successfully');
			}
		} else {
			return redirect()->back();
		}
	}
}
