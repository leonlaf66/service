<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlashHouseData extends Command
{
    protected $signature = 'flash-house-data';
    protected $description = '刷新house-data数据, 仅用于手工执行';

    protected $db;
    protected $db2;
    protected $toTable;

    public function handle()
    {
        // init
        $this->db = app('db');
        $this->db2 = app('db')->connection('pgsql2');
        $this->toTable = $this->db->table('house_data')

        $this->mlsData();
        $this->listhubData();

        app('db')->connection('pgsql2')->disconnect();
        app('db')->disconnect();
    }

    public function mlsData ()
    {
        $self = $this;

        $query = $this->db2->table('mls_rets')
            ->select('list_no', 'json_data')
            ->orderBy('list_no')
            ->chunk(10000, function ($rows) use ($self) {
                foreach ($rows as $row) {
                    $self->flashTo($row->list_no, $row->json_data);
                }
            });
    }

    public function listhubData ()
    {
        $self = $this;

        $query = $this->db2->table('mls_rets_listhub')
            ->select('list_no', 'xml')
            ->orderBy('list_no')
            ->chunk(10000, function ($rows) use ($self) {
                foreach ($rows as $row) {
                    $self->flashTo($row->list_no, $row->xml);
                }
            });
    }

    public function flashTo($listNo, $orgiData)
    {
        // $table = app('db')->table('house_data');

        //if ($table->where('list_no', $listNo)->exists()) {
            /*
            $table->where('list_no', $listNo)->update([
                'orgi_data' => $orgiData
            ]);*/
        //} else {
            $this->toTable->insert([
                'list_no' => $listNo,
                'orgi_data' => $orgiData
            ]);
        //}
    }
}