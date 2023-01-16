<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_clients', function ($t)
        {
          $t->string('identification', 50)->nullable();
          $t->boolean('is_branch')->default(false);
          $t->integer('client_id')->nullable()->unsigned();
          $t->foreign('client_id')
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
