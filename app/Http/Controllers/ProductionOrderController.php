<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;

class ProductionOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productionOrder = ProductionOrder::with('client_id')->get()->toArray();
        return $productionOrder;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $productionOrder = ProductionOrder::create($data);

        return $productionOrder;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        return $productionOrder->toArray();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $productionOrder = ProductionOrder::findOrFail($id);

        $productionOrder->type = array_key_exists('client_id', $data) ? $data['client_id'] : $productionOrder->client_id;
        $productionOrder->observation = array_key_exists('observation', $data) ? $data['observation'] : $productionOrder->observation;

        $productionOrder->save();

        return $productionOrder;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->delete();

        return $productionOrder;
    }
}
