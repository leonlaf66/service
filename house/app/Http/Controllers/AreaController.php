<?php

namespace App\Http\Controllers;

class AreaController extends Controller
{
    public function all ()
    {
        return response()->json([
            'options' => [
                'image_base_url' => env('MEDIA_BASE_URL').'/area/'
            ],
            'items' => get_static_data('areas')
        ]);
    }

    public function hots()
    {
        
    }
}
