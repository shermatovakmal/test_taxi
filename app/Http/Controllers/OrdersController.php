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

    /**
     * @OA\Post(
     * path="/api/order",
     * operationId="order",
     * tags={"Order add"},
     * summary="order add",
     * description="Order add",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"phone", "amount", "latitude_start", "longitude_start", "latitude_end", "longitude_end"},
     *               @OA\Property(property="phone", type="text"),
     *               @OA\Property(property="amount", type="number"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="latitude_start", type="number"),
     *               @OA\Property(property="longitude_start", type="number"),
     *               @OA\Property(property="latitude_end", type="number"),
     *               @OA\Property(property="longitude_end", type="number"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Order created and routes added succesfully",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="true|false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Order created and routes added succesfully"
     *                     ),
     *                     @OA\Property(
     *                         property="order_id",
     *                         type="int",
     *                         description="Order id"
     *                     )
     *                 )
     *             )
     *         }
     *       )
     * )
     */
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

    /**
     * @OA\Patch(
     * path="/api/order-assign2driver",
     * operationId="order-assign2driver",
     * tags={"order-assign2driver"},
     * summary="order-assign2driver",
     * description="order-assign2driver",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="application/x-www-form-urlencoded",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="number"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Order assigned to driver",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="true|false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Success"
     *                     ),
     *                     @OA\Property(
     *                         property="order_id",
     *                         type="int",
     *                         description="Order id"
     *                     ),
     *                     @OA\Property(
     *                         property="driver_id",
     *                         type="int",
     *                         description="Driver id"
     *                     )
     *                 )
     *             )
     *         }
     *       )
     * )
     */
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
