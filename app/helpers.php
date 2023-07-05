<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

function saveImage($image, $path, $model, $field): void
{
    $image_name = time() . rand(100, 9999) . '.' . $image->getClientOriginalExtension();
    $image->move(public_path($path), $image_name);
    $model->$field = $path . $image_name;
    $model->save();
}

function deleteFile($filepath): void
{
    if (File::exists(public_path($filepath)))
    {
        File::delete(public_path($filepath));
    }
}

function getAddress($lat , $lng)
{
    try {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $lat.','.$lng,
            'sensor' => 'false',
            'key'    => env('GOOGLE_MAPS_API_KEY')
        ]);
        $data = $response->json();
        return $data['results'][0]['formatted_address'];
    }
    catch (\Throwable $th) {
        return null;
    }
}
