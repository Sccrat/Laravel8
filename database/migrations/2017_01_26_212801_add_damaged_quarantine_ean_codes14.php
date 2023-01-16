<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDamagedQuarantineEanCodes14 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_ean_codes14', function (Blueprint $table) {
            $table->boolean('damaged')->default(false);  
            $table->boolean('quarantine')->default(false);  
            $table->boolean('stored')->default(false);  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_ean_codes14', function (Blueprint $table) {
            $table->dropColumn('damaged');
            $table->dropColumn('quarantine');
            $table->dropColumn('stored');
        });
    }
}
