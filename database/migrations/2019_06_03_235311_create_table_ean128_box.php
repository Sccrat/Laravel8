<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEan128Box extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_ean128_box', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code128box')->nullable();
            $table->integer('code128_id')->nullable();
            $table->integer('code14_id')->nullable();
        });

        Schema::table('wms_ean_codes14', function ($table)
        {
            $table->integer('ean128_box')->default(0);
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
