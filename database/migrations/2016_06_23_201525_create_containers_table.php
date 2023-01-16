<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wms_containers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            $table->decimal('width', 15, 2)->default(0);
            $table->decimal('height', 15, 2)->default(0);
            $table->decimal('depth', 15, 2)->default(0);
            $table->decimal('weight', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->string('content_type',50);
            $table->string('description',200);
            $table->integer('container_type_id')->unsigned();
            $table->timestamps();
            $table->foreign('container_type_id')
            ->references('id')->on('wms_container_types')
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
        Schema::drop('wms_containers');
    }
}
