<?php
namespace App\Contracts;

interface HouseFieldContract
{
    public function getValue($house, $name, $opt);
    public function getEntity($house, $name, & $opt = []);
    public function getDetails($house);
}