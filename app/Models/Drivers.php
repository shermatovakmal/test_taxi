<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Drivers extends Model
{
    protected $fillable = [
        'name_first',
    ];

    public function getAssignedDriverByOrderStatus($statusArr)
    {
        $resp = array();
        try {
            $sql = "SELECT d.id driver_id, o.id order_id,d.name_first, d.name_last, d.name_additional, d.phone, d.email,
                d.last_status, d.status_updated_at, d.last_balance, d.balance_updated_at, d.rating
                FROM drivers d
                INNER JOIN order_assigned oa ON oa.driver_id=d.id
                INNER JOIN orders o on oa.order_id = o.id
                WHERE o.last_status IN ('" . implode("', '", $statusArr) . "')";

            $drivers = DB::select($sql);

            if(empty($drivers)) throw new \Exception("Водитель с указанными условиями не найден", -1);

            foreach ($drivers as $driver) {
                $resp[$driver->order_id] = array('driver_id' => $driver->driver_id,
                    'name_first' => $driver->name_first,
                    'name_last' => $driver->name_last,
                    'name_additional' => $driver->name_additional,
                    'phone' => $driver->phone,
                    'email' => $driver->email,
                    'last_status' => $driver->last_status,
                    'status_updated_at' => $driver->status_updated_at,
                    'last_balance' => $driver->last_balance,
                    'balance_updated_at' => $driver->balance_updated_at,
                    'rating' => $driver->rating);
            }
        }catch (\Exception $ex){
            //log failed result
        }

        return $resp;
    }
}
