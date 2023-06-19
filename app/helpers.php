<?php

use Illuminate\Support\Facades\File;

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
