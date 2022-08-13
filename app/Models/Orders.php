<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Orders extends Model
{
    /**
     * commission must be fetched from DB
     * @var mixed
     */
    private $commission = 0.1;

    public function getOrdersByStatus($statusArr)
    {
        $sql = "SELECT o.id, o.created_at, CONCAT_WS(', ', rt.latitude_start, rt.longitude_start) start_location,
                o.amount, o.phone, o.last_status, o.status_updated_at
                FROM orders o
                INNER JOIN order_route rt ON rt.order_id=o.id AND rt.last_status='Active' AND rt.id=(SELECT MIN(rt1.id)
                                                                                                    FROM order_route rt1
                                                                                                    WHERE rt1.order_id=rt.order_id
                                                                                                      AND rt1.last_status='Active')
                WHERE o.last_status IN ('".implode("', '", $statusArr)."')
                ORDER BY o.created_at";

        return DB::select($sql);
    }

    public function assignDriver($orderId)
    {
        try {
            //start order processing
            $updOrderQ = "UPDATE orders SET last_status = 'Processing', status_updated_at=NOW() WHERE id=? AND last_status='Received' LIMIT 1";
            $updOrder = DB::update($updOrderQ, array($orderId));
            if($updOrder != 1) throw new \Exception('Заказ не найден', -1);

            //get Order Details
            $getOrderDetailQ = "SELECT o.id, o.amount, rt.latitude_start, rt.longitude_start
                            FROM orders o
                            INNER JOIN order_route rt ON (rt.order_id=o.id
                                                              AND rt.last_status='Active'
                                                              AND rt.id=(SELECT MIN(rt1.id)
                                                                    FROM order_route rt1
                                                                    WHERE rt1.order_id=rt.order_id
                                                                      AND rt1.last_status='Active'))
                            WHERE o.id=?
                            AND o.last_status='Processing'";

            $orderDetail = DB::selectOne($getOrderDetailQ, array($orderId));

            if(empty($orderDetail)) throw new \Exception('Заказ не найден', -2);

            //select Driver
            $getDriverDetailQ = "SELECT d.id driver_id, d.last_status, d.last_balance, d.rating, dl.latitude, dl.longitude, ? lat2, ? lon2,
                                   calculate_distance(dl.latitude, dl.longitude, ?, ?) distance,
                                   calculate_distance(dl.latitude, dl.longitude, ?, ?)/d.rating dist_rating
                                   FROM drivers d
                                       INNER JOIN driver_location dl ON (dl.driver_id=d.id
                                                                             AND dl.created_at=(SELECT MAX(dl1.created_at)
                                                                             FROM driver_location dl1
                                                                             WHERE dl.driver_id = dl1.driver_id))
                                    WHERE d.last_status='Active'
                                      AND d.last_balance > ?*?
                                    ORDER BY 10, 9";
            $bindArr = array($orderDetail->latitude_start,
                $orderDetail->longitude_start,
                $orderDetail->latitude_start,
                $orderDetail->longitude_start,
                $orderDetail->latitude_start,
                $orderDetail->longitude_start,
                $this->commission,
                $orderDetail->amount);
            $driverDetail = DB::selectOne($getDriverDetailQ, $bindArr);

            DB::beginTransaction();

            //add assigned driver
            $insQ = "INSERT INTO order_assigned (order_id, driver_id) VALUES (?, ?)";
            $assignArr = array($orderId, $driverDetail->driver_id);
            $ins = DB::insert($insQ, $assignArr);
            if(!$ins) throw new \Exception("Ошибка при вводе данных назначения заказа к водителю", -3);

            //finish order processing
            $updOrderQ = "UPDATE orders SET last_status = 'Assigned', status_updated_at=NOW() WHERE id=? LIMIT 1";
            $updOrder = DB::update($updOrderQ, array($orderId));
            if($updOrder != 1) throw new \Exception("Ошибка обновления статуса заказа при назначении заказа к водителю", -4);

            $updDriverQ = "UPDATE drivers SET last_status = 'Assigned', status_updated_at=NOW() WHERE id=? LIMIT 1";
            $updDriver = DB::update($updDriverQ, array($driverDetail->driver_id));
            if($updDriver != 1) throw new \Exception("Ошибка обновления статуса водителя при назначении заказа к водителю", -5);

            DB::commit();

            $resp = array('order_id' => $orderId,
                'driver_id' => $driverDetail->driver_id,
                'return_code' => 1,
                'return_status' => 'success',
                'return_text' => 'Success');

            //here need log success result
        }catch (\Exception $ex){

            DB::rollBack();

            $updOrderQ = "UPDATE orders SET last_status = 'Received', status_updated_at=NOW() WHERE id=? AND last_status='Processing' LIMIT 1";
            $updOrder = DB::update($updOrderQ, array($orderId));

            $resp = array('order_id' => $orderId,
                'return_code' => ($ex->getCode() > 0 ? -1*$ex->getCode() : $ex->getCode()),
                'return_status' => 'danger',
                'return_text' => 'Exception: '.$ex->getMessage());

            //here need log failed result
        }

        return $resp;
    }

    public function deliverOrder($orderId)
    {
        //need add logging
        try{
            $orderQ = "SELECT o.id order_id, o.amount, o.last_status, oa.driver_id,
                        (SELECT a.id FROM accounts a WHERE a.account_name = 'internal_cash') company_id
                        FROM orders o
                        INNER JOIN order_assigned oa ON oa.order_id = o.id
                        WHERE o.id = ?";
            $order = DB::selectOne($orderQ, array($orderId));

            if(empty($order)) throw new \Exception('Заказ не найден', -1);
            if($order->last_status != 'Ongoing') throw new \Exception('Статус заказа '.$order->last_status, -2);

            DB::beginTransaction();

            $commissionSum = $order->amount * $this->commission;

            //add transaction info
            $insTransactionQ = "INSERT INTO account_transactions (from_type, from_id, to_type, to_id, order_id, amount, description)
                            VALUES ('driver', ?, 'company', ?, ?, ?, ?)";
            $insTransactionArr = array(
                $order->driver_id,
                $order->company_id,
                $orderId,
                $commissionSum,
                'auto transaction on order delivery: '.$order->amount.'*'.$this->commission);

            $insTransaction = DB::insert($insTransactionQ, $insTransactionArr);
            if(!$insTransaction) throw new \Exception("Ошибка добавления записи о тразакции при закрытии заказа", -3);

            //update order
            $updOrderQ = "UPDATE orders SET last_status = 'Delivered', status_updated_at=NOW() WHERE id=? AND last_status='Ongoing' LIMIT 1";
            $updOrder = DB::update($updOrderQ, array($orderId));
            if($updOrder != 1) throw new \Exception("Ошибка обновления статуса заказа при закрытии заказа", -4);

            //update driver
            $updDriverBalanceQ = "UPDATE drivers
                                    SET last_balance = last_balance - ?,
                                        balance_updated_at = NOW(),
                                        last_status = 'Active',
                                        status_updated_at=NOW()
                                    WHERE id=?
                                      AND last_status='Assigned'
                                    LIMIT 1";
            $updDriverArr = array($commissionSum, $order->driver_id);
            $updDriverBalance = DB::update($updDriverBalanceQ, $updDriverArr);
            if($updDriverBalance != 1) throw new \Exception("Ошибка обновления баланса водителя при закрытии заказа", -5);

            //update company account
            $updCompanyBalanceQ = "UPDATE accounts
                                    SET last_balance = last_balance + ?,
                                        balance_updated_at = NOW()
                                    WHERE id=?
                                    LIMIT 1";
            $updCompanyArr = array($commissionSum, $order->company_id);
            $updCompanyBalance = DB::update($updCompanyBalanceQ, $updCompanyArr);
            if($updCompanyBalance != 1) throw new \Exception("Ошибка обновления баланса компании при закрытии заказа", -6);


            DB::commit();


            $resp = array('order_id' => $orderId,
                'driver_id' => $order->driver_id,
                'company_id' => $order->company_id,
                'commission' => $commissionSum,
                'return_code' => 1,
                'return_status' => 'success',
                'return_text' => 'Success');

        }catch (\Exception $ex){
            DB::rollBack();

            $resp = array('order_id' => $orderId,
                'return_code' => ($ex->getCode() > 0 ? -1*$ex->getCode() : $ex->getCode()),
                'return_status' => 'danger',
                'return_text' => 'Exception: '.$ex->getMessage());
        }

        return $resp;
    }
}
