<?php

namespace App\Http\Controllers\Admin;

use App\{
    Models\Rider,
    Models\Withdraw,
    Models\Transaction,
    Classes\GeniusMailer,
};

use Illuminate\{
    Http\Request,
    Support\Str
};

use Carbon\Carbon;
use Validator;
use Datatables;


class RiderController extends AdminBaseController
{
    //*** JSON Request
    public function datatables()
    {
        $datas = Rider::with('orders')->latest('id')->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('total_delivery', function (Rider $data) {
                return $data->orders->count();
            })
            ->addColumn('action', function (Rider $data) {
                $class = $data->status == 0 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                $ban = '<select class="process select droplinks ' . $class . '">' .
                    '<option data-val="0" value="' . route('admin-rider-ban', ['id1' => $data->id, 'id2' => 1]) . '" ' . $s . '>' . __("Block") . '</option>' .
                    '<option data-val="1" value="' . route('admin-rider-ban', ['id1' => $data->id, 'id2' => 0]) . '" ' . $ns . '>' . __("UnBlock") . '</option></select>';


                return '<div class="action-list">
                            <a href="javascript:;" class="send" data-email="' . $data->email . '" data-toggle="modal" data-target="#vendorform">
                            <i class="fas fa-envelope"></i> ' . __("Send") . '
                            </a>'
                    . $ban .
                    '
                    <a href="' . route('admin-rider-show', $data->id) . '" >
                        <i class="fas fa-eye"></i> ' . __("Details") . '
                    </a>
                    
                    <a href="javascript:;" data-href="' . route('admin-rider-delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete">
                            <i class="fas fa-trash-alt"></i>
                            </a>
                            
                        </div>';
            })
            ->rawColumns(['action', 'total_delivery'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index()
    {
        return view('admin.rider.index');
    }



    public function withdraws()
    {
        return view('admin.rider.withdraws');
    }

    //*** GET Request
    public function show($id)
    {
        $data = Rider::findOrFail($id);
        return view('admin.rider.show', compact('data'));
    }

    //*** GET Request
    public function ban($id1, $id2)
    {
        $user = Rider::findOrFail($id1);
        $user->status = $id2;
        $user->update();
    }



    //*** GET Request Delete
    public function destroy($id)
    {
        $user = Rider::findOrFail($id);
        return true;
        $user->delete();

        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends    
    }

    //*** JSON Request
    public function withdrawdatatables()
    {
        $datas = Withdraw::where('type', '=', 'rider')->latest('id')->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('email', function (Withdraw $data) {
                $email = $data->rider->email;
                return $email;
            })
            ->addColumn('phone', function (Withdraw $data) {
                $phone = $data->rider->phone;
                return $phone;
            })
            ->editColumn('status', function (Withdraw $data) {
                $status = ucfirst($data->status);
                return $status;
            })
            ->editColumn('amount', function (Withdraw $data) {
                $sign = $this->curr;
                $amount = $data->amount * $sign->value;
                return \PriceHelper::showAdminCurrencyPrice($amount);;
            })
            ->addColumn('action', function (Withdraw $data) {
                $action = '<div class="action-list"><a data-href="' . route('admin-withdraw-rider-show', $data->id) . '" class="view details-width" data-toggle="modal" data-target="#modal1"> <i class="fas fa-eye"></i> ' . __("Details") . '</a>';
                if ($data->status == "pending") {
                    $action .= '<a data-href="' . route('admin-withdraw-rider-accept', $data->id) . '" data-toggle="modal" data-target="#status-modal1"> <i class="fas fa-check"></i> ' . __("Accept") . '</a><a data-href="' . route('admin-withdraw-rider-reject', $data->id) . '" data-toggle="modal" data-target="#status-modal"> <i class="fas fa-trash-alt"></i> ' . __("Reject") . '</a>';
                }
                $action .= '</div>';
                return $action;
            })
            ->rawColumns(['name', 'action'])
            ->toJson(); //--- Returning Json Data To Client Side
    }


    //*** GET Request       
    public function withdrawdetails($id)
    {
        $sign = $this->curr;
        $withdraw = Withdraw::findOrFail($id);
        return view('admin.rider.withdraw-details', compact('withdraw', 'sign'));
    }

    //*** GET Request   
    public function accept($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $data['status'] = "completed";
        $withdraw->update($data);
        //--- Redirect Section     
        $msg = __('Withdraw Accepted Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends   
    }

    //*** GET Request   
    public function reject($id)
    {
        $withdraw = Withdraw::findOrFail($id);
        $account = Rider::findOrFail($withdraw->rider->id);
        $account->balance = $account->balance + $withdraw->amount + $withdraw->fee;
        $account->update();
        $data['status'] = "rejected";
        $withdraw->update($data);
        //--- Redirect Section     
        $msg = __('Withdraw Rejected Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends   
    }
}
