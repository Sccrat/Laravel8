<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveForeignWmsProductTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_product_types', function ($table)
        {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn('product_category_id');
        });

        Schema::drop('wms_product_categories');
        Schema::drop('wms_product_sub_types');
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
