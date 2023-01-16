<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_product_types', function ($q)
      {
        $q->integer('product_category_id')->unsigned()->nullable();

        $q->foreign('product_category_id')->references('id')->on('wms_product_categories');
        // $table->foreign('brand_id')->references('id')->on('wms_brands');
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
