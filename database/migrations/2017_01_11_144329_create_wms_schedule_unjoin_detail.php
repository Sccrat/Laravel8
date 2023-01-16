<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWmsScheduleUnjoinDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_schedules MODIFY COLUMN schedule_type ENUM('receipt','deliver','task', 'stock', 'transform','unjoin')");
        Schema::create('wms_schedule_unjoin_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_unjoin_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('position_id')->unsigned()->nullable();
            $table->integer('code128_id')->unsigned()->nullable();
            $table->integer('code14_id')->unsigned()->nullable();
            $table->integer('quanty')->unsigned();

            $table->enum('detail_status', ['pendding', 'removed','unjoin']);

            $table->foreign('schedule_unjoin_id')
                ->references('id')->on('wms_schedule_unjoin')
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
        Schema::drop('wms_schedule_unjoin_detail');
    }
}
