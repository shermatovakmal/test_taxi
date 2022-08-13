<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $statusArr = array('Active', 'Inctive');

            $table->id();
            $table->string('account_name', 128)->unique();
            $table->string('account_number', 32)->nullable();
            $table->string('account_description', 255)->nullable();
            $table->enum('last_status', $statusArr)->default('Active')->index();
            $table->timestamp('status_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('last_balance', 12, 4)->default(0)->index();
            $table->timestamp('balance_updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $typeArr = array('driver', 'company');

            $table->id();
            $table->enum('from_type', $typeArr);
            $table->integer('from_id');
            $table->enum('to_type', $typeArr);
            $table->integer('to_id');
            $table->integer('order_id')->nullable(); //we can have transactions not related to orders
            $table->decimal('amount', 12, 4);
            $table->string('description', 255);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(array('from_type', 'from_id'));
            $table->index(array('to_type', 'to_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_transactions');
    }
}
