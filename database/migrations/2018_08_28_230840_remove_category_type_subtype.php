<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCategoryTypeSubtype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_products', function ($table)
        {
            $table->dropForeign(['product_sub_type_id']);
            $table->dropColumn('product_sub_type_id');

            $table->integer('product_type_id')->nullable()->unsigned();


            $table->foreign('product_type_id')
                  ->references('id')->on('wms_product_types')
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
