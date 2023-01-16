<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusAdjustment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        DB::statement("ALTER TABLE wms_document_detail_count MODIFY COLUMN status ENUM('count', 'adjustment', 'validate', 'closed') DEFAULT 'count'");
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
