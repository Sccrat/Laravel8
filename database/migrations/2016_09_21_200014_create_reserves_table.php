<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_reserves', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('position_id')->unsigned();
            $table->integer('reserved_id')->unsigned()->nullable();
            $table->enum('reserve_type', ['client', 'product']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_reserves');
    }
}
