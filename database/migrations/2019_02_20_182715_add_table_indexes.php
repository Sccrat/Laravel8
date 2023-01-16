<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_products', function ($table)
        {
            $table->index('ean');
            $table->index('reference');
        });

        Schema::table('wms_ean_codes128', function ($table)
        {
            $table->index('code128');
        });

        Schema::table('wms_ean_codes14', function ($table)
        {
            $table->index('code14');
        });

        Schema::table('wms_containers', function ($table)
        {
            $table->index('code');
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
