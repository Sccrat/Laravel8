<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDocumentsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      //Drop columns for documents
      Schema::table('wms_documents', function ($t)
      {
        $t->dropColumn('order_number');
        $t->dropColumn('order_internal');
        $t->dropColumn('remision');
        $t->dropColumn('agent');
        $t->dropColumn('start_date');
        $t->dropColumn('final_date');
        $t->dropColumn('code');
        $t->dropColumn('identification');
        $t->dropColumn('bill_number');
        $t->dropColumn('phone_number');
        $t->dropColumn('list');
        $t->dropColumn('address');
        $t->dropColumn('pay_method');
        $t->dropColumn('sell_type');
        $t->dropColumn('document');
        $t->dropColumn('delivery_site');
        $t->dropColumn('delivery_address');
        $t->dropColumn('assistant_code');
        $t->dropColumn('delivery_time');
        $t->dropColumn('client_name');
        $t->dropColumn('sub_total');
        $t->dropColumn('iva');
        $t->dropColumn('ret_fuente');
        $t->dropColumn('ret_iva');
        $t->dropColumn('trm');
        $t->dropColumn('weight');
        $t->dropColumn('discount');
        $t->dropColumn('zone');
        $t->dropColumn('observations');
      });

      Schema::table('wms_document_details', function ($t)
      {
        $t->dropColumn('number');
        $t->dropColumn('plu');
        $t->dropColumn('pvp');
        $t->dropColumn('value');
        $t->dropColumn('iva');
        $t->dropColumn('discount');
        $t->dropColumn('ret_fuente');
        $t->dropColumn('ret_iva');
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
