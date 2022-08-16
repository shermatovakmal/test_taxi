<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use App\Models\OrderRoute;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{

    public function index()
    {
        $orderObj = new Orders();
        $statusArr = array('Received', 'Processing', 'Assigned');
        $orders = $orderObj->getOrdersByStatus($statusArr);

        $driverObj = new Drivers();
        $statusArr = array('Assigned');
        $drivers = $driverObj->getAssignedDriverByOrderStatus($statusArr);

        $availableDrivers = Drivers::all()->where('last_status','Active')->count();

        $resp = array('orders' => $orders,
            'drivers' => $drivers,
            'availableDriversCount' => $availableDrivers);

        return view('orders.list', $resp);
    }

    public function assignDriver(Request $request)
    {
        $orderObj = new Orders();

        $ordId = intval($request->id);
        if($ordId > 0){
            $resp = $orderObj->assignDriver($ordId);
        }

        return redirect()->route('orders.list');
    }

    public function ongoingOrders()
    {
        $orderObj = new Orders();
        $statusArr = array('Ongoing');
        $orders = $orderObj->getOrdersByStatus($statusArr);

        $resp = array('orders' => $orders,
            'drivers' => array(),
            'availableDriversCount' => 0);

        return view('orders.list_ongoing', $resp);
    }

    public function deliverOrder(Request $request)
    {
        $orderObj = new Orders();

        $ordId = intval($request->id);
        if($ordId > 0){
            $resp = $orderObj->deliverOrder($ordId);

        }

        return redirect()->route('orders.ongoing');
    }


    public function deliveredOrders()
    {
        $orderObj = new Orders();
        $statusArr = array('Delivered');
        $orders = $orderObj->getOrdersByStatus($statusArr);

        $resp = array('orders' => $orders,
            'drivers' => array(),
            'availableDriversCount' => 0);

        return view('orders.list_delivered', $resp);
    }

    public function createOrderJson(Request $request)
    {
        $input = $request->only(['phone', 'amount', 'email', 'latitude_start', 'longitude_start', 'latitude_end', 'longitude_end']);

        $validate_data = [
            'phone' => 'required|string|min:7',
            'amount' => 'required|regex:/^[0-9]+(\.[0-9]{1,16})?$/',
            'email' => 'email',
            'latitude_start' => 'required|regex:/^[0-9]+(\.[0-9]{1,16})?$/',
            'longitude_start' => 'required|regex:/^[0-9]+(\.[0-9]{1,16})?$/',
            'latitude_end' => 'required|regex:/^[0-9]+(\.[0-9]{1,16})?$/',
            'longitude_end' => 'required|regex:/^[0-9]+(\.[0-9]{1,16})?$/',

        ];

        try {
            $validator = Validator::make($input, $validate_data);
            if ($validator->fails()) throw new \Exception($validator->errors());

            $orderObj = Orders::create([
                'phone' => $input['phone'],
                'email' => ($input['email'] ?? null),
                'amount' => $input['amount'],
            ]);

            if (!isset($orderObj->id) || intval($orderObj->id) <= 0) throw new \Exception("Order not created");

            $routeObj = OrderRoute::create([
                'order_id' => $orderObj->id,
                'latitude_start' => $input['latitude_start'],
                'longitude_start' => $input['longitude_start'],
                'latitude_end' => $input['latitude_end'],
                'longitude_end' => $input['longitude_end'],
            ]);

            if (!isset($routeObj->id) || intval($routeObj->id) <= 0) throw new \Exception("Route not added");


            $resp = array(
                'data'=>array(
                    'success' => true,
                    'message' => 'Order created and routes added succesfully',
                    'order_id' => $orderObj->id
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

    public function assignDriverJson(Request $request)
    {
        $input = $request->only(['id']);
        //return response()->json($request);

        $validate_data = [
            'id' => 'required|regex:/^[0-9]+$/',
        ];

        try{
            $validator = Validator::make($input, $validate_data);
            if ($validator->fails()) throw new \Exception($validator->errors());

            $orderObj = new Orders();

            $ordId = intval($input['id']);
            if($ordId <= 0) throw new \Exception("Invalid Order id");

            $ret = $orderObj->assignDriver($ordId);

            if($ret['return_code'] < 0) throw new \Exception($ret['return_text']);

            $resp = array(
                'data'=>array(
                    'success' => true,
                    'message' => $ret['return_text'],
                    'order_id' => $ret['order_id'],
                    'driver_id' => $ret['driver_id']
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
