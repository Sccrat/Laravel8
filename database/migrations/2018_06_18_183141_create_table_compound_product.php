<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompoundProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_compound_product', function (Blueprint $table) {
        $table->increments('id');
        $table->string('ean13');
        $table->integer('product_id');
        $table->integer('quanty');
        $table->integer('parent_product_id');
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
