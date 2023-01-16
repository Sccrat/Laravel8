<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number', 50)->nullable();
            $table->date('date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('final_date')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('identification', 50)->nullable();
            $table->string('bill_number', 50)->nullable();
            $table->string('list', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('client', 50)->nullable();
            $table->string('address', 50)->nullable();
            $table->string('seller', 50)->nullable();
            $table->string('pay_method', 50)->nullable();
            $table->string('document', 50)->nullable();
            $table->string('delivery_site', 50)->nullable();
            $table->string('delivery_address', 50)->nullable();
            $table->string('client_name', 50)->nullable();
            $table->double('quanty', 15, 6)->nullable();
            $table->double('total', 15, 6)->nullable();
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('orders');
    }
}
