<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEnlistCondition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_enlist_products', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('city_id')->unsigned();
        $table->integer('product_id')->unsigned();
        $table->integer('quanty')->unsigned();
        $table->enum('status', ['condition', 'relocate']);



        $table->foreign('product_id')->references('id')->on('wms_products');

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
