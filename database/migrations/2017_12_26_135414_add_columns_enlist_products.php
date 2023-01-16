<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsEnlistProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('wms_enlist_products', function (Blueprint $table) {

          $table->integer('condition')->nullabe();
          $table->integer('semi_condition')->nullabe();
          $table->integer('without_conditioning')->nullabe();
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
