<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleRestock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_schedules MODIFY COLUMN schedule_type ENUM('receipt','deliver','task', 'stock', 'transform','unjoin','restock')");
        Schema::create('wms_schedule_restock', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_id')->unsigned();
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->boolean('relocate_status')->default(false); 
            $table->boolean('status')->default(false);  

            $table->foreign('schedule_id')
                ->references('id')->on('wms_schedules')
                ->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_restock');
    }
}
