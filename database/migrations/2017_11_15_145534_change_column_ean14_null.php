<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnEan14Null extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_samples_detail', function ($table) {
      $table->dropColumn(['ean14_id']);
      });

      Schema::table('wms_samples_detail', function (Blueprint $table) {
        $table->integer('ean14_id')->nullable()->unsigned();
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
