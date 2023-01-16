<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('address', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->boolean('active')->default(true);
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
        Schema::drop('wms_clients');
    }
}
