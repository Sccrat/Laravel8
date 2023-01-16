<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScheduleTransitionAddStockId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_schedule_transition', function (Blueprint $table) {
        $table->integer('stock_id')->unsigned()->nullable();
        $table->integer('transition_id')->unsigned()->nullable()->change();

        $table->foreign('stock_id')
        ->references('id')->on('wms_stock');
        
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
