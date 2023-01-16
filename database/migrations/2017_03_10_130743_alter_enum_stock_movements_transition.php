<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEnumStockMovementsTransitionFinal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wms_stock_movements MODIFY COLUMN concept ENUM('storage', 'relocate', 'transform', 'adjustment', 'dispatch', 'pickin', 'join','unjoin','inspection')");
        DB::statement("ALTER TABLE wms_stock_transition MODIFY COLUMN concept ENUM('storage', 'relocate', 'transform', 'adjustment', 'dispatch', 'pickin','unjoin','inspection')");
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
