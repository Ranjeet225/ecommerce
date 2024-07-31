<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CityController extends Controller
{
    public function managecity($city_id)
    {
        $state = State::findOrFail($city_id);
        return view('admin.country.state.city.index', compact('state'));
    }

    //*** JSON Request
    public function datatables($state_id)
    {
        $datas = City::with('state')->orderBy('id', 'desc')->where('state_id', $state_id)->get();
        //--- Integrating This Collection Into Datatables
        return DataTables::of($datas)
            ->addColumn('action', function (City $data) use ($state_id) {
                return '<div class="action-list"><a href="' . route('admin-city-index', $state_id) . '"><i class="fas fa-city"></i> Manage City</a><a data-href="' . route('admin-city-edit', $data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>Edit</a><a href="javascript:;" data-href="' . route('admin-city-delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })

            ->editColumn('state_id', function (City $data) {
                $state = $data->state->state;
                return  $state;
            })

            ->addColumn('status', function (City $data) {
                $class = $data->status == 1 ? 'drop-success' : 'drop-danger';
                $s = $data->status == 1 ? 'selected' : '';
                $ns = $data->status == 0 ? 'selected' : '';
                return '<div class="action-list"><select class="process select droplinks ' . $class . '"><option data-val="1" value="' . route('admin-city-status', [$data->id, 1]) . '" ' . $s . '>Activated</option><option data-val="0" value="' . route('admin-city-status', [$data->id, 0]) . '" ' . $ns . '>Deactivated</option>/select></div>';
            })

            ->rawColumns(['action', 'status', 'state_id'])
            ->toJson(); //--- Returning Json Data To Client Side
    }




    public function create($state_id)
    {
        $state = State::findOrFail($state_id);
        return view('admin.country.state.city.create', compact('state'));
    }


    public function store(Request $request, $state_id)
    {
        $rules = [
            'city_name'  => 'required|unique:cities,city_name',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $state = new City();
        $state->city_name = $request->city_name;
        $state->state_id = $state_id;
        $state->status = 1;
        $state->save();
        $mgs = __('Data Added Successfully.');
        return response()->json($mgs);
    }


    //*** GET Request Status
    public function status($id1, $id2)
    {
        $city = City::findOrFail($id1);
        $city->status = $id2;
        $city->update();
    }


    public function edit($id)
    {
        $city = City::findOrFail($id);
        return view('admin.country.state.city.edit', compact('city'));
    }


    public function update(Request $request, $id)
    {
        $rules = [
            'city_name'  => 'required|unique:cities,city_name,' . $id,
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }

        $city = City::findOrFail($id);
        $city->city_name = $request->city_name;
        $city->update();
        $mgs = __('Data Updated Successfully.');
        return response()->json($mgs);
    }


    public function delete($id)
    {
        $state = State::findOrFail($id);
        $state->delete();
        $mgs = __('Data Deleted Successfully.');
        return response()->json($mgs);
    }
}
