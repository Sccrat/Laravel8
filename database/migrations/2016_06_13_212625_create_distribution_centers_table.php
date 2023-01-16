<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributionCentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_distribution_centers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 50);            
            $table->integer('city_id')->unsigned();
            $table->string('address');
            $table->boolean('active')->default(true);
            $table->timestamps();

            //Foreing keys
            // $table->foreign('city_id')
            //       ->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_distribution_centers');
    }
}
