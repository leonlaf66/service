<?php
$router->get('/listhub/deficiency/{field}', function (\Illuminate\Http\Request $req, $field) {
    $day = $req->get('day');

    $startDay = date('Y-m-d');
    if ($day) {
        $startDay = date('Y-m-d', strtotime($day));
    }
    $endDay = date('Y-m-d', strtotime($startDay.' +1 day'));

    return app('db')->table('listhub_cases')
        ->where('updated_at', '>=', $startDay.' 00:00:00')
        ->where('updated_at', '<', $endDay . ' 00:00:00')
        ->whereRaw("unkowns @> '{".$field."}'")
        ->get()
        ->map(function ($d) {
            return $d->list_no;
        })
        ->implode(',');
});