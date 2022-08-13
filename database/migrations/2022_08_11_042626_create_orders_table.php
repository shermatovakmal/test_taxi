<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            $statusArr = array('Received', 'Processing', 'Assigned', 'Ongoing', 'Delivered', 'Closed', 'Need reassign');

            $table->id();
            $table->string('phone', 32)->index();
            $table->double('amount');
            $table->string('email', 255)->nullable();
            $table->enum('last_status', $statusArr)->default('Received')->index();
            $table->timestamp('status_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('order_route', function (Blueprint $table) {

            $statusArr = array('Active', 'Inactive');

            $table->id();
            $table->integer('order_id')->index();
            $table->decimal('latitude_start', 19, 16);
            $table->decimal('longitude_start', 19, 16);
            $table->decimal('latitude_end', 19, 16);
            $table->decimal('longitude_end', 19, 16);
            $table->enum('last_status', $statusArr)->default('Active')->index();
            $table->timestamp('status_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('order_assigned', function (Blueprint $table) {
            $statusArr = array('Active', 'Inactive');

            $table->id();
            $table->integer('order_id')->index();
            $table->integer('driver_id')->index();
            $table->enum('last_status', $statusArr)->default('Active')->index(); //if change status to 'Inactive', order status must become 'Need reassign'
            $table->timestamp('status_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('status_change_reason', 255)->default('autoassign'); //need add reason if last_status changed
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_route');
    }
}
