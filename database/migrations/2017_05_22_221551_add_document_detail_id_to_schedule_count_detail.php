<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentDetailIdToScheduleCountDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE wms_schedule_count_detail
                        DROP FOREIGN KEY wms_schedule_count_detail_product_id_foreign,
                        MODIFY product_id INT UNSIGNED;');
        // DB::statement('ALTER TABLE `wms_schedule_count_detail` D `product_id` INT;');
        Schema::table('wms_schedule_count_detail', function (Blueprint $table) {
            $table->integer('document_detail_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wms_schedule_count_detail', function (Blueprint $table) {
            //
        });
    }
}
