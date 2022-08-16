<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use App\Models\OrderRoute;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriversController extends Controller
{
    public function createDriverJson(Request $request)
    {
        $input = $request->only(['name_first', 'name_last', 'name_additional', 'phone', 'email', 'last_status', 'last_balance', 'rating']);

        $validate_data = [
            'name_first' => 'required|string|min:3',
            'name_last' => 'string|min:3',
            'name_additional' => 'string|min:3',
            'phone' => 'string|min:3',
            'email' => 'email',
            'last_status' => 'string|min:3',
            'last_balance' => 'regex:/^[0-9]+(\.[0-9]{1,16})?$/',
            'rating' => 'regex:/^[0]+(\.[0-9]{1,2})?$/',
        ];

        try {
            $validator = Validator::make($input, $validate_data);
            if ($validator->fails()) throw new \Exception($validator->errors());

            $driverObj = Drivers::create([
                'name_first' => $input['name_first'],
                'name_last' => ($input['name_last'] ?? null),
                'name_additional' => ($input['name_additional'] ?? null),
                'phone' => ($input['phone'] ?? null),
                'email' => ($input['email'] ?? null),
                'last_balance' => ($input['email'] ?? 0),
                'rating' => ($input['email'] ?? 0.01),
            ]);

            if (!isset($driverObj->id) || intval($driverObj->id) <= 0) throw new \Exception("Driver not added");

            $resp = array(
                'data'=>array(
                    'success' => true,
                    'message' => 'Driver created succesfully',
                    'order_id' => $driverObj->id
                ),
                'status' => 200);

        }catch (\Exception $ex){

            $resp = array(
                'data'=>array(
                    'success' => false,
                    'message' => 'Exception',
                    'errors' => $ex->getMessage()
                ),
                'status' => 404);

        }

        return response()->json($resp['data'], $resp['status']);
    }
}
