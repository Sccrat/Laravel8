<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\TransportAppointment;

class TransportAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        
        $transportappointment = TransportAppointment::create($data);
        return $transportappointment;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $transport = TransportAppointment::findOrFail($id);

        $transport->date_start = array_key_exists('date_start', $data) ? $data['date_start'] : $transport->date_start;
        $transport->date_end = array_key_exists('date_end', $data) ? $data['date_end'] : $transport->date_end;
        $transport->client_id = array_key_exists('client_id', $data) ? $data['client_id'] : $transport->client_id;
        $transport->sector = array_key_exists('sector', $data) ? $data['sector'] : $transport->sector;
        $transport->phone = array_key_exists('phone', $data) ? $data['phone'] : $transport->phone;
        $transport->document_id = array_key_exists('document_id', $data) ? $data['document_id'] : $transport->document_id;
        $transport->driver_id = array_key_exists('driver_id', $data) ? $data['driver_id'] : $transport->driver_id;
        $transport->vehicle_id = array_key_exists('vehicle_id', $data) ? $data['vehicle_id'] : $transport->vehicle_id;

        $transport->save();

        return $transport;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
