<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSchedulePacking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_packing', function (Blueprint $table) {

        $table->increments('id');
        // $table->integer('schedule_id');
        // $table->integer('zone_position_id');
        $table->integer('document_id');
        $table->string('code14_id');
        $table->integer('quanty');
        $table->integer('product_id');

        // $table->integer('quanty')->unsigned();

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
