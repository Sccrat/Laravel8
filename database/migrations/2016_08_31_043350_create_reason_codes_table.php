<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReasonCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_reason_codes', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name',50);
          $table->string('description',200);
          $table->string('code',10);
          $table->boolean('active')->default(true);
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
