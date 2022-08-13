<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use App\Models\Orders;
use Illuminate\Http\Request;

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

}
