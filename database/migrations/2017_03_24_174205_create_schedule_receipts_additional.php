<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduleReceiptsAdditional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_schedule_receipts_additional', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('schedule_receipt_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('document_id')->unsigned()->nullable();
            $table->boolean('approve_additional')->default(false);
            $table->boolean('active')->default(true);

            $table->foreign('product_id')->references('id')->on('wms_products')->onDelete('cascade');
            $table->foreign('schedule_receipt_id','fk_receipt_add')->references('id')->on('wms_schedule_receipts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_schedule_receipts_additional');
    }
}
