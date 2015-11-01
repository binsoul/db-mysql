<?php

namespace BinSoul\Db\MySQL;

use BinSoul\Db\ConnectionSettings;
use BinSoul\Db\Platform;

/**
 * Implements the {@see Platform} interface for the MySQL platform.
 */
class MySQLPlatform implements Platform
{
    public function buildConnection(ConnectionSettings $settings)
    {
        if (!($settings instanceof MySQLConnectionSettings)) {
            throw new \InvalidArgumentException('Invalid connection parameters given');
        }

        return new DefaultConnection($settings, extension_loaded('mysqlnd'));
    }

    public function getStatementBuilder()
    {
        return new DefaultStatementBuilder();
    }
}
