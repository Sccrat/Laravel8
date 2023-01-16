<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnOrderNumberPackingList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_ean14_packing_list', function (Blueprint $table) {
            $table->string('order_number', 50)->change();
        });
        //wms_packing_list
        Schema::table('wms_packing_list', function (Blueprint $table) {
            $table->string('order_number', 50)->change();
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
