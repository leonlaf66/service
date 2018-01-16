<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class Pager
{
    public static function load($query, $page = 1, $pageSize = 15, $callable)
    {
        $total = $query->count();
        $page = intval($page);
        $pageSize = intval($pageSize);

        $query->limit($pageSize)
            ->offset(($page - 1) * $pageSize);

        $items = $callable($query->get());

        return [
            'total' => $total,
            'current' => $page,
            'page_size' => $pageSize,
            'page_count' => ceil($total * 1.0 / $pageSize),
            'items' => $items
        ];
    }
}