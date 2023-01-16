<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZonePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_zone_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level')->default(0);
            $table->integer('module')->default(0);
            $table->integer('row')->default(0);
            $table->string('position', 5)->default('A');
            $table->string('description', 50);
            $table->string('code', 50);
            $table->decimal('width', 15, 2)->default(0);
            $table->decimal('height', 15, 2)->default(0);
            $table->decimal('depth', 15, 2)->default(0);
            $table->decimal('weight', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            //Foreign
            $table->integer('zone_id')->unsigned();
            $table->foreign('zone_id')
                  ->references('id')->on('wms_zones')
                  ->onDelete('cascade');

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
        Schema::drop('wms_zone_positions');
    }
}
