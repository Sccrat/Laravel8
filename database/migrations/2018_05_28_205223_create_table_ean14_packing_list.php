<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEan14PackingList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_ean14_packing_list', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('product_id');
        $table->integer('code14_id');
        $table->integer('document_id');
        $table->integer('schedule_id');
        $table->integer('order_number');
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
