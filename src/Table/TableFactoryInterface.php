<?php

namespace App\Table;

use Kilik\TableBundle\Components\Table;

interface TableFactoryInterface
{
    public function getTableId(): string;

    public function getTable(): Table;

    public function getExpectedRole(): string;
}
