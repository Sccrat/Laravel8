<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_stock', function ($table) {
          $table->renameColumn('position_id', 'zone_position_id');
          $table->foreign('product_id')
                ->references('id')->on('wms_products')
                ->onDelete('cascade');

                $table->foreign('zone_position_id')
                      ->references('id')->on('wms_zone_positions')
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
