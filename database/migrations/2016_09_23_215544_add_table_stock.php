<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::create('wms_stock', function (Blueprint $table) {
           $table->increments('id');
           $table->integer('product_id')->unsigned();
           $table->integer('position_id')->unsigned();
           $table->integer('code128_id')->unsigned()->nullable();
           $table->integer('code14_id')->unsigned()->nullable();
           $table->integer('quanty')->unsigned();
           $table->timestamps();
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
