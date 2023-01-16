<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class ClientController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companyId = $request->input('company_id');
        $clients = Client::with('client', 'city', 'document')
            ->where('company_id', $companyId)
            // ->where('third_type', 'client')
            ->orderBy('name')->get();
        return $clients->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        // return $data;
//        $time = $data['time'];
        // $data['customer_country_id'] = $data['customer_country']['id'];
        // $data['shipping_country_id'] = $data['shipping_country']['id'];

        // $data['customer_city_id'] = $data['customer_city']['id'];
        // $data['shipping_city_id'] = $data['shipping_city']['id'];

        // $data['customer_state'] = array_key_exists('customer_state', $data) ? $data['customer_state']['district'] : '';
        // $data['shipping_state'] = array_key_exists('shipping_state', $data) ? $data['shipping_state']['district'] : '';

        Client::create($data);
        return $this->response->created();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client = Client::with('city')->where('id',$id)->first();

        // if ($client->customer_country_id) {
        //     $client->customer_country = Country::find($client->customer_country_id);
        // }

        // if ($client->shipping_country_id) {
        //     $client->shipping_country = Country::find($client->shipping_country_id);
        // }

        // if ($client->customer_state) {
        //     $client->customer_state = DB::table('cities')
        //         ->distinct('district')
        //         ->select('district')
        //         ->where('district', $client->customer_state)
        //         ->first();
        // }

        // if ($client->shipping_state) {
        //     $client->shipping_state = DB::table('cities')
        //         ->distinct('district')
        //         ->select('district')
        //         ->where('country_code', $client->shipping_state)
        //         ->first();
        // }

        // if ($client->customer_city_id) {
        //     $client->customer_city = City::find($client->customer_city_id);
        // }

        // if ($client->shipping_city_id) {
        //     $client->shipping_city = City::find($client->shipping_city_id);
        // }

        return $client->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $client = Client::findOrFail($id);


        $client->name = array_key_exists('name', $data) ? $data['name'] : $client->name;
        $client->address = array_key_exists('address', $data) ? $data['address'] : $client->address;
        $client->email = array_key_exists('email', $data) ? $data['email'] : $client->email;
        $client->phone = array_key_exists('phone', $data) ? $data['phone'] : $client->phone;
        $client->active = array_key_exists('active', $data) ? $data['active'] : $client->active;
        $client->identification = array_key_exists('identification', $data) ? $data['identification'] : $client->identification;
        $client->client_id = array_key_exists('client_id', $data) ? $data['client_id'] : $client->client_id;
        $client->is_branch = array_key_exists('is_branch', $data) ? $data['is_branch'] : $client->is_branch;
        $client->city_id = array_key_exists('city_id', $data) ? $data['city_id'] : $client->city_id;
        $client->social_reason = array_key_exists('social_reason', $data) ? $data['social_reason'] : $client->social_reason;
        $client->address_delivery = array_key_exists('address_delivery', $data) ? $data['address_delivery'] : $client->address_delivery;
        $client->gln_code = array_key_exists('gln_code', $data) ? $data['gln_code'] : $client->gln_code;
        $client->sector = array_key_exists('sector', $data) ? $data['sector'] : $client->sector;
        $client->responsible = array_key_exists('responsible', $data) ? $data['responsible'] : $client->responsible;
        $client->is_vendor = array_key_exists('is_vendor', $data) ? $data['is_vendor'] : $client->is_vendor;

        // $client->customer_names = array_key_exists('customer_names', $data) ? $data['customer_names'] : $client->customer_names;
        // $client->customer_last_names = array_key_exists('customer_last_names', $data) ? $data['customer_last_names'] : $client->customer_last_names;
        // $client->customer_street = array_key_exists('customer_street', $data) ? $data['customer_street'] : $client->customer_street;
        // $client->customer_street_2 = array_key_exists('customer_street_2', $data) ? $data['customer_street_2'] : $client->customer_street_2;
        // $client->customer_country_id = array_key_exists('customer_country', $data) ? $data['customer_country']['id'] : $client->customer_country_id;
        // $client->customer_state = array_key_exists('customer_state', $data) ? $data['customer_state']['district'] : $client->customer_state;
        // $client->customer_city_id = array_key_exists('customer_city', $data) ? $data['customer_city']['id'] : $client->customer_city_id;
        // $client->customer_zip_code = array_key_exists('customer_zip_code', $data) ? $data['customer_zip_code'] : $client->customer_zip_code;

        // $client->shipping_names = array_key_exists('shipping_names', $data) ? $data['shipping_names'] : $client->shipping_names;
        // $client->shipping_last_names = array_key_exists('shipping_last_names', $data) ? $data['shipping_last_names'] : $client->shipping_last_names;
        // $client->shipping_street = array_key_exists('shipping_street', $data) ? $data['shipping_street'] : $client->shipping_street;
        // $client->shipping_street_2 = array_key_exists('shipping_street_2', $data) ? $data['shipping_street_2'] : $client->shipping_street_2;
        // $client->shipping_country_id = array_key_exists('shipping_country', $data) ? $data['shipping_country']['id'] : $client->shipping_country_id;
        // $client->shipping_state = array_key_exists('shipping_state', $data) ? $data['shipping_state'] : $client->shipping_state;
        // $client->shipping_city_id = array_key_exists('shipping_city', $data) ? $data['shipping_city']['id'] : $client->shipping_city_id;
        // $client->shipping_zip_code = array_key_exists('shipping_zip_code', $data) ? $data['shipping_zip_code'] : $client->shipping_zip_code;

        // $client->type = array_key_exists('type', $data) ? $data['type'] : $client->type;
        // $client->phone_2 = array_key_exists('phone_2', $data) ? $data['phone_2'] : $client->phone_2;
        // $client->price_type = array_key_exists('price_type', $data) ? $data['price_type'] : $client->price_type;
        // $client->contact_name_1 = array_key_exists('contact_name_1', $data) ? $data['contact_name_1'] : $client->contact_name_1;
        // $client->contact_phone_1 = array_key_exists('contact_phone_1', $data) ? $data['contact_phone_1'] : $client->contact_phone_1;
        // $client->contact_email_1 = array_key_exists('contact_email_1', $data) ? $data['contact_email_1'] : $client->contact_email_1;
        // $client->contact_name_2 = array_key_exists('contact_name_2', $data) ? $data['contact_name_2'] : $client->contact_name_2;
        // $client->contact_phone_2 = array_key_exists('contact_phone_2', $data) ? $data['contact_phone_2'] : $client->contact_phone_2;
        // $client->contact_email_2 = array_key_exists('contact_email_2', $data) ? $data['contact_email_2'] : $client->contact_email_2;
        // $client->branch_office = array_key_exists('branch_office', $data) ? $data['branch_office'] : $client->branch_office;
        // $client->company_name = array_key_exists('company_name', $data) ? $data['company_name'] : $client->company_name;
        // $client->shipping_company_name = array_key_exists('shipping_company_name', $data) ? $data['shipping_company_name'] : $client->shipping_company_name;


//        $client->contact_name = array_key_exists('contact_name', $data) ? $data['contact_name'] : $client->contact_name;
//        $time = array_key_exists('time', $data) ? $data['time'] : $client->time;

        if (!$client->is_branch) {
            $client->client_id = null;
        }

//        if ($time) {
//
//            // return $client->time;
//
//            $city = City::where('id', $client->city_id)->update(['dispatch_time' => $time]);
//        }

        $client->save();

        return $this->response->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->active = false;
        $client->save();

        return $this->response->noContent();
    }

    public function getVendors(Request $request)
    {
        return Client::where('third_type', 'vendor')
            ->select('company_name', 'customer_street', 'customer_street_2', 'customer_zip_code', 'contact_name_1',
                'contact_phone_1', 'contact_email_1', 'active', 'id')->get()->toArray();

    }

    public function getClientsFilter(Request $request)
    {
        $companyId = $request->input('company_id');
        $clients = Client::where('company_id', $companyId)
            ->where('is_vendor',1)
            ->orderBy('name')->get();
        return $clients->toArray();
    }
}
