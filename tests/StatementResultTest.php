<?php

namespace BinSoul\Test\Db\Platform\MySQL;

use BinSoul\Db\Platform\MySQL\MySQLiStmtFake;
use BinSoul\Db\Platform\MySQL\StatementResult;

class StatementResultTest extends AbstractResultTest
{
    protected function buildResult($withResource = true, $withMetadata = true)
    {
        $stmt = new MySQLiStmtFake();

        return new StatementResult($withResource ? $stmt : null, $withMetadata ? $stmt : null);
    }
}
