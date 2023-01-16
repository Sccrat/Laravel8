<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEanCodes14WithSoftDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wms_ean_codes14', function ($table) {
        $table->softDeletes();
        $table->integer('delete_reason_code_id')->nullable()->unsigned();
        
        $table->foreign('delete_reason_code_id')
          ->references('id')->on('wms_reason_codes');
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
