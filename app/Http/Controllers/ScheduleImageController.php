<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ScheduleImage;
use Image;

use Storage;


class ScheduleImageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $scheduleId = $request->input('schedule_id');
      $scheduleImages = new ScheduleImage;

      if(isset($scheduleId)) {
        $scheduleImages = $scheduleImages->where('schedule_id', $scheduleId);
      }

      $scheduleImages = $scheduleImages->get();

      return $scheduleImages->toArray();
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

      //Create the register on the table
      $scheduleImage = ScheduleImage::create($data);

      //Save physically the path
      $folder = env("AWS_FOLDER");
      Storage::disk('s3')->put($folder.'/'.$data['name'], file_get_contents($data["uri"]), 'public');
      // Image::make(file_get_contents($data["uri"]))->encode('jpg', 50)->save($path);

      //Get the http url
      $scheduleImage->url = $folder.'/'.$data['name'];

      //Update the register on the table
      $scheduleImage->save();

      return  $this->response->created();
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
        //
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
