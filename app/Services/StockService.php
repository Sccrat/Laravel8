<?php 

namespace App\Services;

use App\Models\Stock;
use App\Models\EanCode14Detail;
use App\Models\StockTransition;

class StockService
{
    public function getStockPlain($warehouseId, $zoneId, $clientId, $productType, $reference, $ean128, $ean14, $position) {
        //Nombre zone
        //warehouse (ya está en el filtro),
        //Cliente
        //Producto
        //Fila, Módulo, posición
        //Ean 128, ean14, ean13, ref
        //total

        $stock = Stock::with(
                'product.product_type',
                'zone_position.zone.warehouse',
                'product.client',
                'ean128',
                'ean14.code14_packing.document',
                'ean14.serialLot.batches',
                'ean14.serial' 
            )
            // ->leftJoin('wms_ean_codes14_serial as ser', 'st.code14_id', '=', 'ser.ean_codes14_id')
            // ->leftJoin('wms_batches as batch', 'ser.lot', '=', 'batch.id')
            ->orderBy('zone_position_id')
            ->orderBy('code128_id')
            ->orderBy('code14_id');

        if(isset($zoneId)) {
            $stock = $stock->whereHas('zone_position', function ($q) use ($zoneId) {
                $q->where('zone_id', $zoneId);
            });
        }    

        if (isset($warehouseId)) {
            $stock = $stock->whereHas('zone_position.zone', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if (isset($clientId)) {
            $stock = $stock->whereHas('product.document_detail', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        if (isset($productType)) {
            $stock = $stock->where('product_type_id', $productType);
        }

        if (isset($reference)) {
            $stock = $stock->whereHas('product', function ($q) use ($reference) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                // $q->where('reference', $reference);
                // $q->whereLike('reference', $reference);
                $q->where('reference', 'like', $reference . '%');
            });
        }

        if (isset($ean128)) {
            $stock = $stock->whereHas('ean128', function ($q) use ($ean128) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                $q->where('code128', $ean128);
            });
        }

        if (isset($ean14)) {
            $stock = $stock->whereHas('ean14', function ($q) use ($ean14) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                $q->where('code14', $ean14);
            });
        }

        if (isset($position)) {
            $stock = $stock->whereHas('zone_position', function ($q) use ($position) {
                $q->where('code', $position);
            });
        }

        $sum = $stock->sum('quanty');

        

        $paginate = $stock->paginate(50);

        $sumpage = $paginate->sum('quanty');

        $pagArr = $paginate->toArray();
        $pagArr['total'] = $sum;
        $pagArr['total_page'] = $sumpage;

        return $pagArr;
    }

    public function receiptPlain($warehouse, $client, $ean13, $reference, $productType, $ean14)
    {
        $codes = EanCode14Detail::with('ean_code14.documentDetail.document.schedule_document_receipt.schedule.schedule_receipt','ean_code14.serial', 'product.client', 'ean_code14.pallet.ean128')->whereHas('ean_code14', function ($q) {
            $q->where('stored', false)->where('canceled', false);
        });
    
        if(isset($warehouse)) {
            $codes = $codes->whereHas('ean_code14.documentDetail.document.schedule_document_receipt.schedule.schedule_receipt', function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse);
            });
        }
    
        if(isset($productType)) {
            $codes = $codes->whereHas('ean_code14.documentDetail.product', function ($q) use ($productType) {
                $q->where('product_type_id', $productType);
            });
        }

        if (isset($ean13)) {
            $codes = $codes->orWhereHas('ean_code14.documentDetail.product',function ($q) use ($ean13)
            {
                $q->where('ean','LIKE',$ean13.'%');
            });
        }

        if(isset($reference)) {
            $codes = $codes->whereHas('ean_code14.documentDetail.product', function ($q) use ($reference) {
                $q->where('reference', $reference);
                // $q->where('reference','LIKE', $reference.'%')->orWhere('ean', $reference);
            });
        }

        if(isset($client)) {
            $codes = $codes->whereHas('ean_code14.documentDetail.product', function ($q) use ($client) {
                $q->where('client_id', $client);
            });
        }

        if(isset($ean14)) {
            $codes = $codes->whereHas('ean_code14', function ($q) use ($ean14)
            {
                $q->where('code14', $ean14);
            });
        }

        $sum = $codes->sum('quanty');
        $paginate = $codes->paginate(50);
        $sumpage = $paginate->sum('quanty');
        $pagArr = $paginate->toArray();
        $pagArr['total'] = $sum;
        $pagArr['total_page'] = $sumpage;

        return $pagArr;

        // $codes = $codes->paginate(50);

        // return $codes->toArray();
    }

    public function getTransition($warehouseId, $clientId, $productType, $reference, $ean128, $ean14)
    {
        $stock = StockTransition::with(
            'product.product_type',
            'zone_position.zone.warehouse',
            'product.client',
            'ean128',
            'ean14.code14_packing.document'
        )
        ->orderBy('zone_position_id')
        ->orderBy('code128_id')
        ->orderBy('code14_id');

        if (isset($warehouseId)) {
            $stock = $stock->whereHas('zone_position.zone', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if (isset($clientId)) {
            $stock = $stock->whereHas('product.document_detail', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        if (isset($productType)) {
            $stock = $stock->where('product_type_id', $productType);
        }

        if (isset($reference)) {
            $stock = $stock->whereHas('product', function ($q) use ($reference) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                $q->where('reference', $reference);
            });
        }

        if (isset($ean128)) {
            $stock = $stock->whereHas('ean128', function ($q) use ($ean128) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                $q->where('code128', $ean128);
            });
        }

        if (isset($ean14)) {
            $stock = $stock->whereHas('ean14', function ($q) use ($ean14) {
                // $q->orWhere('reference', $reference)->orWhere('code', $reference)->orWhere('ean', $reference);
                $q->where('code14', $ean14);
            });
        }

        $sum = $stock->sum('quanty');        
        $paginate = $stock->paginate(10);
        $sumpage = $paginate->sum('quanty');

        $pagArr = $paginate->toArray();
        $pagArr['total'] = $sum;
        $pagArr['total_page'] = $sumpage;

        return $pagArr;
    }
}
