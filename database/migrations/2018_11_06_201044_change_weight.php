<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWeight extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wms_products', function ($table)
        // {
        //     $table->decimal('weight',5,4)->change();
        // });

         DB::statement("ALTER TABLE wms_products MODIFY COLUMN  weight decimal(5,4)");

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
