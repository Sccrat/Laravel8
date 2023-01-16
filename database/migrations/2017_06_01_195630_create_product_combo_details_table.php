<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductComboDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_product_combo_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_combo_id')->unsigned();
            $table->integer('product_id')->unsigned();

            $table->foreign('product_combo_id')->references('id')->on('wms_product_combos')->onDelete('cascade');
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
        Schema::drop('wms_product_combo_details');
    }
}
