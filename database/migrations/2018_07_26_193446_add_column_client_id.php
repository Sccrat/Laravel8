<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnClientId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        DB::statement("ALTER TABLE `wms_enlist_products` CHANGE `parent_product_id` `parent_product_id` varchar(50) NULL;");
        Schema::table('wms_enlist_products', function ($table)
        {
            $table->integer('client_id')->nullable();
            // $table->string('parent_product_id')->change();
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
