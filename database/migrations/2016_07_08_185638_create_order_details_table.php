<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_number', 50)->nullable();
            $table->string('reference', 50)->nullable();
            $table->string('size', 10)->nullable();
            $table->string('colour', 50)->nullable();
            $table->string('plu', 50)->nullable();
            $table->string('pvp', 50)->nullable();
            $table->string('ean', 50)->nullable();
            $table->string('description', 100)->nullable();
            $table->double('quanty', 15, 6)->nullable();
            $table->string('package', 50)->nullable();
            $table->double('value', 15, 6)->nullable();
            $table->double('total', 15, 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('order_details');
    }
}
