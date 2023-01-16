<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_samples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id')->unsigned();
            $table->integer('schedule_id')->unsigned();
            $table->integer('document_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('wms_warehouses')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('wms_schedules')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('wms_documents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_samples');
    }
}
