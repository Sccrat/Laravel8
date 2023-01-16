<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\ContentIndicator;

class ContentIndicatorController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $contentindicator = ContentIndicator::with('product','container')->orderBy('content_indicator')->get();
      return $contentindicator->toArray();
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
      ContentIndicator::create($data);
      return $this->response->created();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $contentindicator = ContentIndicator::findOrFail($id);
      return $contentindicator->toArray();
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
      $contentindicator = ContentIndicator::findOrFail($id);

      $contentindicator->content_indicator = array_key_exists('content_indicator', $data) ? $data['content_indicator'] : $contentindicator->content_indicator;
      $contentindicator->quanty = array_key_exists('quanty', $data) ? $data['quanty'] : $contentindicator->quanty;
      $contentindicator->product_id = array_key_exists('product_id', $data) ? $data['product_id'] : $contentindicator->product_id;
      $contentindicator->container_id = array_key_exists('container_id', $data) ? $data['container_id'] : $contentindicator->container_id;
      $contentindicator->save();

      return $this->response->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $contentindicator = ContentIndicator::findOrFail($id);
      $contentindicator->delete();

      return $this->response->noContent();
    }
}
