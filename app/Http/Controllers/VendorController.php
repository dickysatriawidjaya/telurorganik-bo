<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Resources\VendorResource;
use Validator;
use App\Laravue\Models\Vendor;
use Illuminate\Support\Arr;

class VendorController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $vendorQuery = Vendor::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $status = Arr::get($searchParams, 'status', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $paginate = Arr::get($searchParams, 'paginate', '');

        if (!empty($status)) {
            $vendorQuery->where('status',$status);
        }

        if (!empty($keyword)) {
            $vendorQuery->where('name', 'LIKE', '%' . $keyword . '%');
            $vendorQuery->orWhere('address', 'LIKE', '%' . $keyword . '%');
            $vendorQuery->orWhere('pic_name', 'LIKE', '%' . $keyword . '%');
            $vendorQuery->orWhere('phone', 'LIKE', '%' . $keyword . '%');
        }

        $vendorQuery->orderBy('status','DESC');
        if ($paginate == "true") {
            return VendorResource::collection($vendorQuery->paginate($limit));
        } else {
            return VendorResource::collection($vendorQuery->where("status",1)->get());
        }
    }

    public function store(Request $request)
    {
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            [
                'name_form' => 'required',
                'address_form' => 'required',
                'phone_form' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } 

        $check = Vendor::where('name',$request->name_form)->where('status',1)->first();
        if ($check) {
            return response()->json(['errors' => "Vendor Name " .$request->name_form. ' Already Exist'], 403);
        }

        $params = $request->all();
        $params['name']=$request->name_form;
        $params['pic_name']=$request->pic_name_form;
        $params['phone']=$request->phone_form;
        $params['address']=$request->address_form;
        $params['status']=1;
        $vendor = Vendor::create($params);
        return new VendorResource($vendor);
    }

    public function update(Request $request, Vendor $vendor)
    {
        
        if ($vendor === null) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $found = Vendor::where('name', $request->name_form)->where('status',1)->first();

            if ($found && $found->id !== $vendor->id) {
                return response()->json(['error' => 'Vendor name already exist'], 403);
            }else{
                $vendor->name = $request->get('name_form');
                $vendor->pic_name = $request->get('pic_name_form');
                $vendor->phone = $request->get('phone_form');
                $vendor->address = $request->get('address_form');
                $vendor->status = $request->get('status_form');
                $vendor->save();
                return new VendorResource($vendor);
            }
        }
    }

    public function destroy(Vendor $vendor)
    {
        try {
            $vendor->update(['status'=>-1]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }
    
    private function getValidationRules($isNew = true)
    {
        return [
            'name_form' => 'required',
            'address_form' => 'required',
            'phone_form' => 'required',
        ];
    }
}
