<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_schedule_receipts', function ($table)
        {
          $table->string('client', 100)->nullable();
          $table->string('bl', 50)->nullable();
          $table->string('container_number', 50)->nullable();
          $table->double('container_weight', 15, 2)->nullable();
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
