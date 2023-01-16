<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMachineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('wms_machines', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name', 100);
          $table->string('code', 50);
          $table->string('description', 200);
          $table->enum('status', ['active', 'inactive', 'maintenance']);

          $table->decimal('width', 15, 2)->default(0);
          $table->decimal('height', 15, 2)->default(0);
          $table->decimal('depth', 15, 2)->default(0);
          $table->decimal('weight', 15, 2)->default(0);

          $table->integer('zone_id')->unsigned();
          $table->foreign('zone_id')
                ->references('id')->on('wms_zones')
                ->onDelete('cascade');

          $table->integer('machine_type_id')->unsigned();
          $table->foreign('machine_type_id')
                ->references('id')->on('wms_machine_types')
                ->onDelete('cascade');

          $table->integer('responsable_id')->unsigned();
          $table->foreign('responsable_id')
                ->references('id')->on('wms_personal')
                ->onDelete('cascade');

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
        //
    }
}
