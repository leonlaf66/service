<?php
namespace App\Repositories;

class Yellowpage
{
    public function flashSummery($pageId, $path)
    {
        $comments = app('db')->table('comment')
            ->where('page_id', $pageId)
            ->count();

        $totalRating = app('db')->table('comment')
            ->where('page_id', $pageId)
            ->sum('rating');

        if ($comments === 0) {
            return false;
        }

        $rating = round($totalRating * 1.0 / $comments, 0);

        $ypid = explode('/', $path)[1];

        return app('db')->table('yellow_page')
            ->where('id', $ypid)
            ->update([
                'rating' => $rating,
                'comments' => $comments
            ]);
    }
}