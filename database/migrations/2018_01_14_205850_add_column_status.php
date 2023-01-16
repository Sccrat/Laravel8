<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::table('wms_enlist_products', function ($table) {

        $table->dropColumn('status');
        // $table->dropColumn('stock_secure');
      });

      Schema::table('wms_enlist_products', function ($table)
      {
          $table->enum('status', ['planed', 'not_planned'])->default('not_planned');
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
