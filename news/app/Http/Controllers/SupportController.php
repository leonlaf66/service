<?php
namespace App\Http\Controllers;

use App\Repositories\Type as TypeRepository;
use App\Repositories\News as NewsRepository;
use App\Helpers\TypeCollecHelper;
use App\Helpers\NewsCollecHelper;
use Illuminate\Support\Facades\Cache;

class SupportController extends Controller
{
    public function banners()
    {
        return response()->json(
            get_static_data('news-banners/'.area_id(), [])
        );
    }

    public function types(TypeRepository $typeRepository)
    {
        $cahceKey = serialize([
            'news:types',
            area_id(),
        ]);

        // Cache::pull($cahceKey);
        $results = Cache::remember($cahceKey, 0, function () use ($typeRepository) {
            $typeCollec = $typeRepository->all()
                ->get();
            return TypeCollecHelper::build($typeCollec);
        });

        return response()->json($results);
    }
}
