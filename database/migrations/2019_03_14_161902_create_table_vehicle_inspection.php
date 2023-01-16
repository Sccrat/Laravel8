<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVehicleInspection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_vehicle_inspection', function (Blueprint $table) {
        $table->increments('id');
        $table->string('office_name')->nullable();
        $table->string('plate')->nullable();
        $table->string('driver_name')->nullable();
        $table->string('review_date')->nullable();
        $table->string('exterior')->nullable();
        $table->string('tires')->nullable();
        $table->string('front_wall')->nullable();
        $table->string('left_side')->nullable();
        $table->string('right_side')->nullable();
        $table->string('floor')->nullable();
        $table->string('ceiling')->nullable();
        $table->string('doors')->nullable();
        $table->string('chassis')->nullable();
        $table->string('extinguisher')->nullable();
        $table->string('driver')->nullable();
        $table->string('check_vehicle')->nullable();
        $table->string('cameraman')->nullable();
        $table->string('load_participant')->nullable();
        $table->integer('document_id');

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
