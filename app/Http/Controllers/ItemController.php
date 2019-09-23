<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Laravue\Models\Item;
use App\Laravue\Models\Unit;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function storeItem(){
        return "senneg";
        sleep(1);
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'name' => ['required'],
                    'unit_id' => ['required'],
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $save = Item::create([
                'name' => $params['name'],
                'unit_id' => $params['unit_id'],
                'status' => 1,
            ]);
            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            return new UserResource($user);
        }
    }
}
