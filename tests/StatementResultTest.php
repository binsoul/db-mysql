<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\MySQL\MySQLiStmtFake;
use BinSoul\Db\MySQL\StatementResult;

class StatementResultTest extends AbstractResultTest
{
    protected function buildResult($withResource = true, $withMetadata = true)
    {
        $stmt = new MySQLiStmtFake();

        return new StatementResult($withResource ? $stmt : null, $withMetadata ? $stmt : null);
    }
}
