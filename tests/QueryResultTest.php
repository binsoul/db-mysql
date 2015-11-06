<?php

namespace BinSoul\Test\Db\Platform\MySQL;

use BinSoul\Db\Platform\MySQL\MySQLiFake;
use BinSoul\Db\Platform\MySQL\MySQLiResultFake;
use BinSoul\Db\Platform\MySQL\QueryResult;

class QueryResultTest extends AbstractResultTest
{
    protected function buildResult($withResource = true, $withMetadata = true)
    {
        $mysqli = new MySQLiFake();
        $result = new MySQLiResultFake();

        return new QueryResult($withResource ? $result : null, $withMetadata ? $mysqli : null);
    }
}
