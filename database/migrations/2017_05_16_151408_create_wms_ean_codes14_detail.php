<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsEanCodes14Detail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `wms_ean_codes14` CHANGE `product_id` `product_id_deleted` INT;');
        DB::statement('ALTER TABLE `wms_ean_codes14` CHANGE `code13` `code13_deleted` VARCHAR(500);');
        Schema::create('wms_ean_codes14_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ean_code14_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('quanty')->unsigned()->nullable();


            $table->foreign('product_id')
                ->references('id')->on('wms_products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_ean_codes14_detail');
    }
}
