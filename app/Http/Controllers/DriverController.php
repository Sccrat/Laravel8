<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Driver;

class DriverController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $drivers = Driver::orderBy('driver_name');

        $drivers = $drivers->get();
        return $drivers->toArray();
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
        Driver::create($data);
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
        $driver = Driver::findOrFail($id);
        return $driver->toArray();
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
        $driver = Driver::findOrFail($id);

        $driver->driver_name = array_key_exists('driver_name', $data) ? $data['driver_name'] : $driver->driver_name;
        $driver->driver_lastname = array_key_exists('driver_lastname', $data) ? $data['driver_lastname'] : $driver->driver_lastname;
        $driver->driver_identification = array_key_exists('driver_identification', $data) ? $data['driver_identification'] : $driver->driver_identification;
        $driver->driver_phone = array_key_exists('driver_phone', $data) ? $data['driver_phone'] : $driver->driver_phone;

        $driver->save();

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
        $driver = Driver::findOrFail($id);
        $driver->delete();

        return $this->response->noContent();
    }
}
