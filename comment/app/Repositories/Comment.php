<?php
namespace App\Repositories;

class Comment
{
    public function all($path)
    {
        $pageId = app('db')->table('comment_page')
            ->select('id')
            ->where('url', $path)
            ->value('id');

        return app('db')->table('comment')
            ->where('page_id', $pageId)
            ->get();
    }

    public function create($path, $data = [], $userId)
    {
        $rating = array_get($data, 'rating');
        $content = array_get($data, 'content');

        $pageId = app('db')->table('comment_page')
            ->select('id')
            ->where('url', $path)
            ->value('id');

        if (!$pageId) {
            $pageId = app('db')->table('comment_page')
                ->insertGetId([
                    'hash' => md5($path),
                    'url' => $path
                ]);
        }

        $data = [
            'page_id' => $pageId,
            'rating' => intval($rating),
            'user_id' => $userId,
            'comments' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $commentId = app('db')->table('comment')
            ->insertGetId($data);

        if ($commentId) {
            app('App\Repositories\Yellowpage')->flashSummery($pageId, $path);
        }

        return [
            'id' => $commentId,
            'rating' => $data['rating'],
            'content' => $data['comments'],
            'user_id' => $userId,
            'created_at' => $data['created_at']
        ];
    }
}