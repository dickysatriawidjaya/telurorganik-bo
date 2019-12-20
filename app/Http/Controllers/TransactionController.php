<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;
use Validator;
use App\Laravue\Models\Transaction;
use App\Laravue\Models\Transaction_Detail;
use App\Laravue\Models\Vendor;
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
        $month = Arr::get($searchParams, 'month', '');
        $role = Arr::get($searchParams, 'role', '');
        $print = Arr::get($searchParams, 'print', '');


        $start_date = Arr::get($searchParams, 'start_date', '');
        $end_date = Arr::get($searchParams, 'end_date', '');

        if (!empty($status)) {
            return "masuk";
            $transactionQuery->where('status',$status);
        }

        if (!empty($vendor)) {
            $vendor_result = Vendor::where('parent_id',$vendor)->where('status',1)->pluck('id')->toArray();
            array_push($vendor_result,(int)($vendor));
            $transactionQuery->whereIn('vendor_id',$vendor_result);
        }

        if (!empty($start_date)) {
            $transactionQuery->where('transaction_date', '>=', date("Y-m-d",strtotime($start_date)));
        }

        if (!empty($end_date)) {
            $transactionQuery->where('transaction_date', '<=', date("Y-m-d",strtotime($end_date)));
        }

        if (!empty($keyword)) {
            $transactionQuery->where('transaction_no', 'LIKE', '%' . $keyword . '%');
            $transactionQuery->orWhere('total', 'LIKE', '%' . $keyword . '%');
        }

        if ($role != 'admin') {
            $transactionQuery->where('created_at', '<=', date("Y-m-d"));
            $transactionQuery->where('created_at', '>=', date("Y-m-d",strtotime("-30 day")));
        }

        // if (!empty($print)) {
        //   $transactionQuery->orderBy('vendor_id','ASC')->orderBy('id','DESC');
        // }else{
        $transactionQuery->with('vendor','detail_transaction.item.unit')->orderBy('id','DESC');
        // }

        return TransactionResource::collection($transactionQuery->paginate($limit));
    }

    public function print(){
        $searchParams = $request->all();
        $transactionQuery = Transaction::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $status = Arr::get($searchParams, 'status', '');
        $vendor = Arr::get($searchParams, 'vendor', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        $month = Arr::get($searchParams, 'month', '');

        if (!empty($status)) {
            $transactionQuery->where('status',$status);
        }

        if (!empty($vendor)) {
            $transactionQuery->where('vendor_id',$vendor);
        }

        if (!empty($month)) {
            $transactionQuery->whereMonth('transaction_date', '=', $month);
        }

        if (!empty($keyword)) {
            $transactionQuery->where('transaction_no', 'LIKE', '%' . $keyword . '%');
            $transactionQuery->orWhere('total', 'LIKE', '%' . $keyword . '%');
        }

        $transactionQuery->with('vendor','detail_transaction.item');

        return TransactionResource::collection($transactionQuery->paginate($limit));
    }

    public function show(Transaction $transaction)
    {
        $query = Transaction::where('id',$transaction->id)->with('vendor','detail_transaction.item')->first();
        return new TransactionResource($query);
    }

    public function store(Request $request)
    {
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            [
                'transaction_no_form' => 'required',
                'vendor_id_form' => 'required',
                'total_form' => 'required',
                'date_form' => 'required',
                'detail'=>'required',
            ],
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        $check_trans_no = Transaction::where("transaction_no",$request->transaction_no_form)->whereIn('status',[1,-1])->get();

        if(count($check_trans_no) > 0){
            return response()->json(['errors' =>  "Transaction number already exist."],403);
        }

        try {
            $params = $request->all();
            $params['transaction_no']=$request->transaction_no_form;
            $params['vendor_id']=$request->vendor_id_form;
            $params['total']=$request->total_form;
            $params['transaction_date']=date("Y-m-d",strtotime($request->date_form));
            $params['created_at']=date("Y-m-d");
            $params['status']=-1;
            $Transaction = Transaction::create($params);
        } catch (\Throwable $th) {
            return response()->json(['errors' =>  "Failed Save Transaction "], 500);
        }
        //save detail
        try {
            foreach ($request->detail as $key => $d) {
                $newdetail = New Transaction_Detail;
                $newdetail->transaction_id = $Transaction->id;
                if (isset($d['item_id_form'])) {
                  $newdetail->item_id = $d['item_id_form'];
                }else{
                  return response()->json(['errors' =>  "item is required"], 500);
                }
                $newdetail->quantity = $d['quantity_form'];
                $newdetail->discount = $d['discount_form'];
                $newdetail->subtotal = $d['subtotal_form'];
                $newdetail->status = 1;
                $newdetail->save();
             }
        } catch (\Throwable $th) {
            return response()->json(['errors' =>  "Failed Save Detail Transaction "], 500);
        }

        //save detail end
        return new TransactionResource($Transaction);
    }

    // public function changeStatus(Request $request, Transaction $transaction)
    // {
    //     return "bacot";
    //     $transaction->status = $request->get('status_form');
    //     $transaction->save();
    //     return response()->json(['success' => 'Success Update Status Transaction'], 200);
    // }

    public function update(Request $request, Transaction $transaction)
    {

        if ($transaction === null) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {

            try {
                $transaction->transaction_no = $request->get('transaction_no_form');
                $transaction->transaction_date = date("Y-m-d",strtotime($request->get('date_form')));
                $transaction->vendor_id = $request->get('vendor_id_form');
                $transaction->total = $request->get('total_form');
                $transaction->status = $request->get('status_form');
                $transaction->save();
            } catch (\Throwable $th) {
                return response()->json(['errors' => "Failed Update Transaction Data "], 403);
            }

            if ($request->detail) {
                $update = Transaction_Detail::where('transaction_id',$transaction->id)->update(['status' => -1]);
                // try {
                    foreach ($request->detail as $key => $d) {
                      if (isset($d['id'])) {
                        $newdetail = Transaction_Detail::find($d['id']);
                        $newdetail->transaction_id = $transaction->id;
                        if (isset($d['item_id_form'])) {
                          $newdetail->item_id = $d['item_id_form'];
                        }else{
                          return response()->json(['errors' =>  "Item Cannot Be Null"], 500);
                        }
                        $newdetail->quantity = $d['quantity_form'];
                        $newdetail->discount = $d['discount_form'];
                        if (isset($d['retur_form'])) {
                            $newdetail->retur = $d['retur_form'];
                        }
                        $newdetail->subtotal = $d['subtotal_form'];
                        $newdetail->status = 1;
                        $newdetail->save();
                      }else{
                        $newdetail = New Transaction_Detail;
                        $newdetail->transaction_id = $transaction->id;
                        if (isset($d['item_id_form'])) {
                          $newdetail->item_id = $d['item_id_form'];
                        }else{
                          return response()->json(['errors' =>  "Item Cannot Be Null"], 500);
                        }
                        $newdetail->quantity = $d['quantity_form'];
                        $newdetail->discount = $d['discount_form'];
                        $newdetail->subtotal = $d['subtotal_form'];
                        $newdetail->status = 1;
                        $newdetail->save();
                      }
                    }
                // } catch (\Throwable $th) {
                //     return response()->json(['errors' => "Failed Update Transaction Detail Data"], 403);
                // }

            }

            return new TransactionResource($transaction);
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
            'transaction_no_form' => 'required',
            'vendor_id_form' => 'required',
            'total_form' => 'required',
            'detail'=>'required',
            'date_form' => 'required',
        ];
    }
}
