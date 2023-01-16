<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 50);
            $table->string('real_code', 50);
            $table->decimal('weight', 15, 2)->default(0);
            $table->decimal('depth', 15, 2)->default(0);
            $table->decimal('height', 15, 2)->default(0);
            $table->integer('rows')->unsigned();
            $table->integer('levels')->unsigned();
            $table->integer('modules')->unsigned();
            $table->integer('positions')->unsigned();
            $table->integer('warehouse_id')->unsigned();
            $table->integer('zone_type_id')->unsigned();
            $table->boolean('active')->default(true);
            $table->timestamps();

            //foreign
            $table->foreign('warehouse_id')
                  ->references('id')->on('wms_warehouses')
                  ->onDelete('cascade');

            //foreign
            $table->foreign('zone_type_id')
                  ->references('id')->on('wms_zone_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_zones');
    }
}
