<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleTransformAddProductid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_schedule_transform', function (Blueprint $table) {
        $table->integer('product_id')->nullable()->unsigned();

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
