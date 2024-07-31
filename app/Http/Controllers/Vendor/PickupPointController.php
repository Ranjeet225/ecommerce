<?php

namespace App\Http\Controllers\Vendor;

use App\Models\PickupPoint;
use Illuminate\Http\Request;


use Validator;
use Datatables;

class PickupPointController extends VendorBaseController
{
    //*** JSON Request
    public function datatables()
    {
        $datas = PickupPoint::where('user_id', $this->user->id)->get();
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->editColumn('status', function (PickupPoint $data) {
                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                return '<div class="action-list"><select class="process select droplinks ' . $class . '"><option data-val="1" value="' . route('vendor-pickup-point-status', ['id' => $data->id, 'status' => 1]) . '" ' . $s . '>' . __('Activated') . '</option><<option data-val="0" value="' . route('vendor-pickup-point-status', ['id' => $data->id, 'status' => 0]) . '" ' . $ns . '>' . __('Deactivated') . '</option>/select></div>';
            })
            ->addColumn('action', function (PickupPoint $data) {
                return '<div class="action-list"><a data-href="' . route('vendor-pickup-point-edit', $data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>' . __('Edit') . '</a><a href="javascript:;" data-href="' . route('vendor-pickup-point-delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->rawColumns(['action', 'status'])
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index()
    {
        return view('vendor.pickup.index');
    }

    //*** GET Request
    public function create()
    {
        $sign = $this->curr;
        return view('vendor.pickup.create', compact('sign'));
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = ['location' => 'required'];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = new PickupPoint();
        $data->location = $request->location;
        $data->user_id = $this->user->id;
        $data->save();

        $msg = __('New Data Added Successfully.');
        return response()->json($msg);
    }

    //*** GET Request
    public function edit($id)
    {
        $data = PickupPoint::findOrFail($id);
        return view('vendor.pickup.edit', compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        $rules = ['location' => 'required'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $data = PickupPoint::findOrFail($id);
        $data->location = $request->location;
        $data->user_id = $this->user->id;
        $data->save();
        $msg = __('Data Updated Successfully.');
        return response()->json($msg);
    }

    //*** GET Request Delete
    public function destroy($id)
    {
        $data = PickupPoint::findOrFail($id);
        $data->delete();
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
    }

    public function status($id1, $id2)
    {
        $data = PickupPoint::findOrFail($id1);
        $data->status = $id2;
        $data->update();
        //--- Redirect Section
        $msg = __('Status Updated Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends
    }
}
