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
}
