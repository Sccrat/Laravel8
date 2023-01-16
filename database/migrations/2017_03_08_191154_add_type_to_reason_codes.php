<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToReasonCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wms_reason_codes', function (Blueprint $table) {
            $table->enum('type', ['type_receipt', 'type_transform','type_codes'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_reason_codes', function (Blueprint $table) {
            //
        });
    }
}
