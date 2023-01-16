<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_warehouse_features', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feature_id')->unsigned();
            $table->integer('warehouse_id')->unsigned();
            $table->string('comparation', 2)->default('=');
            $table->double('value', 15, 6)->default(0);

            $table->foreign('feature_id')
                  ->references('id')->on('wms_features')
                  ->onDelete('cascade');

            $table->foreign('warehouse_id')
                  ->references('id')->on('wms_warehouses')
                  ->onDelete('cascade');
            //'feature_id', 'warehouse_id', 'comparation', 'value'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('wms_warehouse_features');
    }
}
