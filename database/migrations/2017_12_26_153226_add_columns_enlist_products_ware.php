<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsEnlistProductsWare extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_enlist_products', function (Blueprint $table) {

        $table->integer('condition_warehouse')->nullabe();
        $table->integer('semi_condition_warehouse')->nullabe();
        $table->integer('without_conditioning_warehouse')->nullabe();

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
