<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;
use Validator;
use App\Laravue\Models\Transaction;
use App\Laravue\Models\Transaction_Detail;
use Illuminate\Support\Arr;

class TransactionController extends Controller
{
    const ITEM_PER_PAGE = 15;

    public function index(Request $request)
    {
        $searchParams = $request->all();
        $transactionQuery = Transaction::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $status = Arr::get($searchParams, 'status', '');
        $vendor = Arr::get($searchParams, 'vendor', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        
        if (!empty($status)) {
            $transactionQuery->where('status',$status);
        }

        if (!empty($vendor)) {
            $transactionQuery->where('vendor_id',$vendor);
        }

        if (!empty($keyword)) {
            $transactionQuery->where('transaction_no', 'LIKE', '%' . $keyword . '%');
            $transactionQuery->orWhere('total', 'LIKE', '%' . $keyword . '%');
        }

        $transactionQuery->with('vendor','detail_transaction');

        return TransactionResource::collection($transactionQuery->paginate($limit));
    }

    public function show(Transaction $transaction)
    {
        $query = Transaction::where('id',$transaction->id)->with('vendor','detail_transaction')->first();
        return new TransactionResource($query);
    }

    public function store(Request $request)
    {
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            [
                'name_form' => 'required',
                'unit_id_form' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } 

        $check = Transaction::where('name',$request->name_form)->where('status',1)->first();
        if ($check) {
            return response()->json(['errors' => "Transaction Name " .$request->name_form. ' Already Exist'], 403);
        }

        $params = $request->all();
        $params['name']=$request->name_form;
        $params['unit_id']=$request->unit_id_form;
        $params['status']=1;
        $Transaction = Transaction::create($params);
        return new TransactionResource($Transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        
        if ($item === null) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            $found = Transaction::where('name', $request->name_form)->where('status',1)->first();

            if ($found && $found->id !== $item->id) {
                return response()->json(['error' => 'Transaction name already exist'], 403);
            }else{
                $item->name = $request->get('name_form');
                $item->unit_id = $request->get('unit_id_form');
                $item->status = $request->get('status_form');
                $item->save();
                return new TransactionResource($item);
            }
        }
    }

    public function destroy(Transaction $Transaction)
    {
        try {
            $Transaction->update(['status'=>-1]);
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
        ];
    }
}
