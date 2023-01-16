<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTypeAddComplementWmsSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_sizes', function (Blueprint $table) {
            $table->dropColumn('size_type');
            $table->boolean('is_complement')->default(false);         
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_sizes', function (Blueprint $table) {
            //
        });
    }
}
