<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableValidatePlanClose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_plan_close', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('document_id')->nullable();
        $table->integer('product_id')->nullable();
        $table->integer('order_quanty')->nullable();
        $table->integer('own_quanty')->nullable();
        $table->string('observation')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
