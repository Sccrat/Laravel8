<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonCodeToEancode128 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

      public function up()
      {
        Schema::table('wms_ean_codes128', function ($table) {

            $table->integer('reason_code_id')->unsigned()->nullable();
            $table->foreign('reason_code_id')
                  ->references('id')->on('wms_reason_codes')
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
        //
    }
}
