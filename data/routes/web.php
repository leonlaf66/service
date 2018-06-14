<?php
$router->get('/listhub/deficiency/{field}', function (\Illuminate\Http\Request $req, $field) {
    redirect('/listhub/deficiency/');
});
$router->get('/listhub/deficiency/', function (\Illuminate\Http\Request $req) {
    $day = $req->get('day');

    if (!$day) {
        $day = app('db')->table('listhub_cases')
            ->max('updated_at');
    }

    $startDay = date('Y-m-d', strtotime($day));
    $endDay = date('Y-m-d', strtotime($startDay.' +1 day'));

    return app('db')->table('listhub_cases')
        ->where('updated_at', '>=', $startDay.' 00:00:00')
        ->where('updated_at', '<', $endDay . ' 00:00:00')
        ->get()
        ->map(function ($d) {
            return $d->list_no;
        })
        ->implode(',');
});