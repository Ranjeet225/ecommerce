<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use DB;
use App;
use Auth;
use Session;

class RiderBaseController extends Controller
{
    protected $gs;
    protected $curr;
    protected $language_id;
    protected $rider;

    public function __construct()
    {
        $this->middleware('auth:rider');

        // Set Global GeneralSettings
        $this->gs = DB::table('generalsettings')->find(1);

        $this->middleware(function ($request, $next) {

        // Set Global Users
        $this->rider = Auth::guard('rider')->user();

            // Set Global Language

            if (Session::has('language')) 
            {
                $this->language = DB::table('languages')->find(Session::get('language'));
            }
            else
            {
                $this->language = DB::table('languages')->where('is_default','=',1)->first();
            }  
            view()->share('langg', $this->language);
            App::setlocale($this->language->name);
    
            // Set Global Currency
    
            if (Session::has('currency')) {
                $this->curr = DB::table('currencies')->find(Session::get('currency'));
            }
            else {
                $this->curr = DB::table('currencies')->where('is_default','=',1)->first();
            }
    
            return $next($request);
        });
    }

}
