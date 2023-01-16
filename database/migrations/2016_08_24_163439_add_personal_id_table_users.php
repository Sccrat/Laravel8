<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersonalIdTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
       Schema::table('users', function($table)
       {
         $table->integer('personal_id')->nullable()->unsigned();

         $table->foreign('personal_id')
               ->references('id')->on('wms_personal');
               //->onDelete('cascade');
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
