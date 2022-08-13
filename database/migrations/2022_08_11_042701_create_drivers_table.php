<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $statusArr = array('New', 'Resolution required', 'Active', 'Suspended', 'Inactive');

            $table->id();
            $table->string('name_first', 64);
            $table->string('name_last', 64)->nullable();
            $table->string('name_additional', 128)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email', 255)->nullable();
            $table->enum('last_status', $statusArr)->default('New')->index();
            $table->timestamp('status_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('last_balance', 10, 4)->default(0)->index();
            $table->timestamp('balance_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->decimal('rating', 4, 2)->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('driver_location', function (Blueprint $table) {
            $table->id();
            $table->integer('driver_id')->index();
            $table->decimal('latitude', 19, 16);
            $table->decimal('longitude', 19, 16);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
        });

        //source: https://www.movable-type.co.uk/scripts/latlong.html
        DB::unprepared("CREATE FUNCTION calculate_distance(lat1 DECIMAL(19,16), lon1 DECIMAL(19,16), lat2 DECIMAL(19,16), lon2 DECIMAL(19,16))
                    RETURNS DOUBLE
                       DETERMINISTIC
                        BEGIN
                            DECLARE f1 DECIMAL(19,16);
                            DECLARE f2 DECIMAL(19,16);
                            DECLARE delta_f DECIMAL(19,16);
                            DECLARE delta_l DECIMAL(19,16);
                            DECLARE a DECIMAL(22,20);

                            SET f1 = lat1 * pi() / 180;
                            SET f2 = lat2 * pi() / 180;
                            SET delta_f = (lat2 - lat1) * pi() / 180;
                            SET delta_l = (lon2 - lon1) * pi() / 180;
                            SET a = sin(delta_f/2) * sin(delta_f/2) + cos(f1) * cos(f2) * sin(delta_l/2) * sin(delta_l/2);

                            RETURN 6371000 * 2 * ATAN2(SQRT(a), SQRT(1-a));

                        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('driver_location');
        DB::unprepared('DROP FUNCTION IF EXISTS calculate_distance');
    }
}
