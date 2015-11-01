<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\MySQL\MySQLiFake;
use BinSoul\Db\MySQL\MySQLiResultFake;
use BinSoul\Db\MySQL\QueryResult;

class QueryResultTest extends AbstractResultTest
{
    protected function buildResult($withResource = true, $withMetadata = true)
    {
        $mysqli = new MySQLiFake();
        $result = new MySQLiResultFake();

        return new QueryResult($withResource ? $result : null, $withMetadata ? $mysqli : null);
    }
}
