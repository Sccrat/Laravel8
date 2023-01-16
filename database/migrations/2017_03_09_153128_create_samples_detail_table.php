<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_samples_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('samples_id')->unsigned();
            $table->integer('ean14_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned();
            $table->double('weight_reference', 15, 8);

            $table->foreign('samples_id')->references('id')->on('wms_samples')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('wms_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_samples_detail');
    }
}
