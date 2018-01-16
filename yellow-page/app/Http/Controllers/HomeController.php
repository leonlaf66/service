<?php
namespace App\Http\Controllers;

use App\Repositories\Type as TypeRepository;
use App\Repositories\Item as ItemRepository;
use App\Helpers\TypeCollecHelper;
use App\Helpers\ItemCollecHelper;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(TypeRepository $typeRepository, ItemRepository $itemRepository)
    {
        $cahceKey = serialize([
            'yellowpage:index',
            area_id(),
            lang_id()
        ]);

        Cache::forget($cahceKey);
        $results = Cache::remember($cahceKey, 1440, function () use ($typeRepository, $itemRepository) {
            $typeCollec = $typeRepository->all()->get();
            $types = TypeCollecHelper::build($typeCollec);

            $topItemCollec = $itemRepository->tops(area_id())->get();
            $topItems = ItemCollecHelper::build($topItemCollec);

            return [
                'types' => $types,
                'topItems' => $topItems
            ];
        });

        return response()->json($results);
    }
}
