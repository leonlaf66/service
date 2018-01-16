<?php
namespace App\Http\Controllers;

use App\Repositories\News as NewsRepository;

class ItemController extends Controller
{
    public function get(NewsRepository $newsRepository, $id)
    {
        $results = $newsRepository->get(area_id(), $id);

        return response()->json($results);
    }
}
