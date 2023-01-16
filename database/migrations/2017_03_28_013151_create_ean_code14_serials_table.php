<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEanCode14SerialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_ean_codes14_serial', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ean_codes14_id')->unsigned();
            $table->string('serial', 100);
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
        Schema::drop('wms_ean_codes14_serial');
    }
}
