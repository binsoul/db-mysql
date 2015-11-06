<?php

namespace BinSoul\Db\Platform\MySQL;

use BinSoul\Db\Connection;
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

    public function getDefinitionProvider(Connection $connection)
    {
        return new DefaultDefinitionProvider($connection);
    }
}
