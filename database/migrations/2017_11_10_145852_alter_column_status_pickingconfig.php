<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStatusPickingconfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::statement("ALTER TABLE wms_stock_picking_config MODIFY COLUMN status ENUM('process','closed','','available')");
        Schema::table('wms_stock_picking_config', function ($table) {
        $table->dropColumn(['status']);
        });

        Schema::table('wms_stock_picking_config', function ($t)
        {
          $t->enum('status', ['process','closed','available'])->default('process');
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
