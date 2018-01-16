<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ItemCollecHelper;
use App\Repositories\Item as ItemRepository;

class ListController extends Controller
{
    public function all(ItemRepository $itemRepository, Request $req, $typeId)
    {
        $page = $req->get('page', 1);
        $pageSize = $req->get('page_size', 1500);

        $itemQuery = $itemRepository->all(area_id(), $typeId, $page, $pageSize);
        $itemCollec = $itemQuery->get();

        return response()->json([
            'total' => $itemQuery->count(),
            'items' => ItemCollecHelper::build($itemCollec)
        ]);
    }
}
