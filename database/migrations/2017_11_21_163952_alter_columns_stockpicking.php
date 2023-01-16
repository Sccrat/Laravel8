<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsStockpicking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Schema::table('wms_stock_picking_config', function (Blueprint $table) {
          // $table->integer('product_id')->nullable()->unsigned()->change();
          // $table->integer('min_stock')->nullable()->unsigned()->change();
          // $table->integer('stock_secure')->nullable()->unsigned()->change();
          DB::statement('ALTER TABLE wms_stock_picking_config MODIFY product_id INT(10) UNSIGNED NULL, MODIFY min_stock INT(10) NULL, MODIFY stock_secure INT(10) NULL;');

      // });
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
