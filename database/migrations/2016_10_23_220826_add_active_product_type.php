<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveProductType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_products', function (Blueprint $t)
        {
          $t->boolean('active')->default(true);
        });

        Schema::table('wms_product_types', function (Blueprint $t)
        {
          $t->boolean('active')->default(true); 
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
