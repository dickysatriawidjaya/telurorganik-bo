<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\UnitResource;
use Validator;
use App\Laravue\Models\Unit;
use Illuminate\Support\Arr;

class UnitController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $unitQuery = Unit::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $status = Arr::get($searchParams, 'status', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $paginate = Arr::get($searchParams, 'paginate', '');

        if (!empty($status)) {
            $unitQuery->where('status',$status);
        }

        if (!empty($keyword)) {
            $unitQuery->where('name', 'LIKE', '%' . $keyword . '%');
            $unitQuery->orWhere('unit_code', 'LIKE', '%' . $keyword . '%');
            $unitQuery->orWhere('description', 'LIKE', '%' . $keyword . '%');
        }

        $unitQuery->orderBy('status','DESC');
        if ($paginate == "true") {
            return UnitResource::collection($unitQuery->paginate($limit));
        } else {
            return UnitResource::collection($unitQuery->where("status",1)->get());
        }
    }

    public function store(Request $request)
    {
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            [
                'name_form' => 'required',
                'unit_code_form' => 'required',
                'description_form' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        $check = Unit::where('name',$request->name_form)->where('status',1)->first();
        if ($check) {
            return response()->json(['errors' => "Unit Name " .$request->name_form. ' Already Exist'], 403);
        }

        $params = $request->all();
        $params['name']=$request->name_form;
        $params['unit_code']=$request->unit_code_form;
        $params['description']=$request->description_form;
        $params['status']=1;
        $unit = Unit::create($params);
        return new UnitResource($unit);
    }

    public function update(Request $request, Unit $unit)
    {

        if ($unit === null) {
            return response()->json(['error' => 'unit not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $found = unit::where('name', $request->name_form)->where('status',1)->first();

            if ($found && $found->id !== $unit->id) {
                return response()->json(['error' => 'unit name already exist'], 403);
            }else{
                $unit->name = $request->get('name_form');
                $unit->unit_code = $request->get('unit_code_form');
                $unit->description = $request->get('description_form');
                $unit->status = $request->get('status_form');
                $unit->save();
                return new UnitResource($unit);
            }
        }
    }

    public function destroy(unit $unit)
    {
        try {
            $unit->update(['status'=>-1]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    private function getValidationRules($isNew = true)
    {
        return [
            'name_form' => 'required',
            'unit_code_form' => 'required',
        ];
    }
}
