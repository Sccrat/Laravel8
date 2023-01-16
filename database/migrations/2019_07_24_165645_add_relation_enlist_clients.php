<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationEnlistClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_enlist_products', function ($table)
        {
            // $table->integer('client_id')->unsigned()->change();
            DB::statement("ALTER TABLE `wms_enlist_products` CHANGE COLUMN `client_id` `client_id` INT(11) UNSIGNED");
            $table->foreign('client_id')
                ->references('id')->on('wms_clients')
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
        //
    }
}
