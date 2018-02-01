<?php
namespace App\Contracts;

interface HousingContract
{
    public function search($q, $filters, $sorts);
    public function items($ids);
    public function get($id);
}