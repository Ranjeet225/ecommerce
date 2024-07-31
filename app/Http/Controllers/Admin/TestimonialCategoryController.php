<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogCategory;
use App\Models\TestimonialCategory;

use Illuminate\Http\Request;
use Validator;
use Datatables;

class TestimonialCategoryController extends AdminBaseController
{
    //*** JSON Request
    public function datatables()
    {
        // dd('kjjk');
        $datas = TestimonialCategory::latest('id')->get();
      
        //--- Integrating This Collection Into Datatables
        return Datatables::of($datas)
            ->addColumn('action', function (TestimonialCategory $data) {
                return '<div class="action-list"><a data-href="' . route('admin-ctestimonial-edit', $data->id) . '" class="edit" data-toggle="modal" data-target="#modal1"> <i class="fas fa-edit"></i>' . __('Edit') . '</a><a href="javascript:;" data-href="' . route('admin-ctestimonial-delete', $data->id) . '" data-toggle="modal" data-target="#confirm-delete" class="delete"><i class="fas fa-trash-alt"></i></a></div>';
            })
            ->toJson(); //--- Returning Json Data To Client Side
    }

    public function index()
    {

      
        return view('admin.testimonial.category.index');
    }

    public function create()
    {
        return view('admin.testimonial.category.create');
    }

    //*** POST Request
    public function store(Request $request)
    {
        //--- Validation Section
        $rules = [
            'name' => 'unique:blog_categories',
            'slug' => 'unique:blog_categories'
        ];
        $customs = [
            'name.unique' => __('This name has already been taken.'),
            'slug.unique' => __('This slug has already been taken.')
        ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = new TestimonialCategory;
        $input = $request->all();
        $data->fill($input)->save();
        //--- Logic Section Ends

        //--- Redirect Section  
        $msg = __('New Data Added Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends  
    }

    //*** GET Request
    public function edit($id)
    {
        $data = TestimonialCategory::findOrFail($id);
        return view('admin.testimonial.category.edit', compact('data'));
    }

    //*** POST Request
    public function update(Request $request, $id)
    {
        //--- Validation Section
        $rules = [
            'name' => 'unique:blog_categories,name,' . $id,
            'slug' => 'unique:blog_categories,slug,' . $id
        ];
        $customs = [
            'name.unique' => __('This name has already been taken.'),
            'slug.unique' => __('This slug has already been taken.')
        ];
        $validator = Validator::make($request->all(), $rules, $customs);
        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends

        //--- Logic Section
        $data = TestimonialCategory::findOrFail($id);
        $input = $request->all();
        $data->update($input);
        //--- Logic Section Ends

        //--- Redirect Section          
        $msg = __('Data Updated Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends  

    }

    //*** GET Request
    public function destroy($id)
    {
        $data = BlogCategory::findOrFail($id);

        //--- Check If there any blogs available, If Available Then Delete it 
        if ($data->blogs->count() > 0) {
            foreach ($data->blogs as $element) {
                $element->delete();
            }
        }
        $data->delete();
        //--- Redirect Section     
        $msg = __('Data Deleted Successfully.');
        return response()->json($msg);
        //--- Redirect Section Ends   
    }
}
