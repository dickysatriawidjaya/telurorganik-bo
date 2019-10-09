<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ItemResource;
use Validator;
use App\Laravue\Models\Item;
use Illuminate\Support\Arr;

class ItemController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $itemQuery = Item::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $status = Arr::get($searchParams, 'status', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $paginate = Arr::get($searchParams, 'paginate', '');
        
        if (!empty($status)) {
            $itemQuery->where('status',$status);
        }

        if (!empty($keyword)) {
            $itemQuery->where('name', 'LIKE', '%' . $keyword . '%');
        }

        $itemQuery->orderBy('status','DESC')->with('unit');
        // return response()->json($itemQuery->paginate($limit);
        if ($paginate == true) {
            return ItemResource::collection($itemQuery->paginate($limit));
        } else {
            return ItemResource::collection($itemQuery->get());
        }
        
    }

    public function store(Request $request)
    {
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            [
                'name_form' => 'required',
                'unit_id_form' => 'required',
                'price_form' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } 

        $check = Item::where('name',$request->name_form)->where('status',1)->first();
        if ($check) {
            return response()->json(['errors' => "Item Name " .$request->name_form. ' Already Exist'], 403);
        }

        $params = $request->all();
        $params['name']=$request->name_form;
        $params['unit_id']=$request->unit_id_form;
        $params['price']=$request->price_form;
        $params['status']=1;
        $Item = Item::create($params);
        return new ItemResource($Item);
    }

    public function update(Request $request, Item $item)
    {
        
        if ($item === null) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $found = Item::where('name', $request->name_form)->where('status',1)->first();

            if ($found && $found->id !== $item->id) {
                return response()->json(['error' => 'Item name already exist'], 403);
            }else{
                $item->name = $request->get('name_form');
                $item->unit_id = $request->get('unit_id_form');
                $item->status = $request->get('status_form');
                $item->price = $request->get('price_form');
                $item->save();
                return new ItemResource($item);
            }
        }
    }

    public function destroy(Item $Item)
    {
        try {
            $Item->update(['status'=>-1]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }
    
    private function getValidationRules($isNew = true)
    {
        return [
            'name_form' => 'required',
            'unit_id_form' => 'required',
            'price_form' => 'required',
        ];
    }
}
