<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 50);
            $table->string('real_code', 50);
            $table->string('address', 50);
            $table->integer('distribution_center_id')->unsigned();
            $table->decimal('weight', 15, 2)->default(0);
            $table->decimal('depth', 15, 2)->default(0);
            $table->decimal('height', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            //foreign
            $table->foreign('distribution_center_id')
                  ->references('id')->on('wms_distribution_centers')
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
        Schema::drop('wms_warehouses');
    }
}
