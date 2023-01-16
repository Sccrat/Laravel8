<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuantyReceivedEnlistProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_enlist_products', function ($table)
        {
            $table->integer('picked_quanty')->nullable();
        });

        Schema::table('wms_eancodes14_packing', function ($table)
        {
            $table->integer('quanty')->default(0)->nullable();
            $table->integer('stock_id')->nullable();
            $table->integer('code128_id')->nullable();
            $table->integer('zone_position_last_id')->nullable();
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
