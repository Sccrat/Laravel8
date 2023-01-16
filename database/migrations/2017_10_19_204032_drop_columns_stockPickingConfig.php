<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsStockPickingConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_stock_picking_config', function ($table) {
        $table->dropColumn(['status', 'reason_codes_id']);
        });

        Schema::table('wms_stock_picking_config_product', function ($table) {
        $table->dropColumn(['quanty_transition']);
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
