<?php
namespace App\Http\Controllers;

use App\Repositories\News as NewsRepository;
use App\Helpers\NewsCollecHelper;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function all(Request $req, NewsRepository $newsRepository)
    {
        $typeId = $req->get('type_id');
        $page = $req->get('page', 1);
        $pageSize = $req->get('page_size', 15);

        $itemQuery = $newsRepository->all(area_id(), $typeId, $page, $pageSize);
        $itemCollec = $itemQuery->get();

        $items = NewsCollecHelper::build($itemCollec);

        return response()->json([
            'total' => $itemQuery->count(),
            'items' => $items
        ]);
    }
}
