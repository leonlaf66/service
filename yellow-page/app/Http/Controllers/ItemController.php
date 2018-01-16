<?php
namespace App\Http\Controllers;

use App\Repositories\Item as ItemRepository;

class ItemController extends Controller
{
    public function get(ItemRepository $itemRepository, $id)
    {
        $result = $itemRepository->get(area_id(), $id);
        $result->business = tt($result->business);

        return response()->json($result);
    }
}
