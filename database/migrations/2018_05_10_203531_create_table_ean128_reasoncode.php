<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEan128Reasoncode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_ean128_reason_codes', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('code128_id');
      $table->integer('reason_code_id');
      $table->integer('schedule_id');
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
