<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZoneFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_zone_features', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feature_id')->unsigned();
            $table->integer('zone_id')->unsigned();
            $table->string('comparation', 2)->default('=');
            $table->double('value', 15, 6)->default(0);

            $table->foreign('feature_id')
                  ->references('id')->on('wms_features')
                  ->onDelete('cascade');

            $table->foreign('zone_id')
                  ->references('id')->on('wms_zones')
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
        Schema::drop('wms_zone_features');
    }
}
