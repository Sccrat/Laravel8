<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductSubtype extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_products', function ($t)
        {
          $t->integer('product_sub_type_id')->unsigned()->nullable();

          $t->foreign('product_sub_type_id')
            ->references('id')->on('wms_product_sub_types')
            ->onDelete('cascade');


          $t->dropForeign('wms_products_product_type_id_foreign');

          $t->dropColumn('product_type_id');
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
