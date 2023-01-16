<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDispatchProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_dispatch_products', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('product_id');
      $table->integer('warehouse_id');
      $table->integer('quanty');
      $table->integer('document_id');
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
