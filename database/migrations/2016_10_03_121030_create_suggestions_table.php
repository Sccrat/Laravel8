<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_suggestions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->integer('zone_position_id')->unsigned();
            $table->boolean('stored')->default(false);
            $table->timestamps();

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
        Schema::drop('wms_suggestions');
    }
}
