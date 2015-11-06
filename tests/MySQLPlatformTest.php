<?php

namespace BinSoul\Test\Db\Platform\MySQL;

use BinSoul\Db\Connection;
use BinSoul\Db\ConnectionSettings;
use BinSoul\Db\DefinitionProvider;
use BinSoul\Db\Platform\MySQL\DefaultConnection;
use BinSoul\Db\Platform\MySQL\MySQLConnectionSettings;
use BinSoul\Db\Platform\MySQL\MySQLPlatform;
use BinSoul\Db\Platform\MySQL\DefaultStatementBuilder;

class MySQLPlatformTest extends \PHPUnit_Framework_TestCase
{
    public function test_builds_connection()
    {
        $platform = new MySQLPlatform();
        $connection = $platform->buildConnection(MySQLConnectionSettings::fromArray([]));
        $this->assertInstanceOf(DefaultConnection::class, $connection);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_throws_exception_for_invalid_settings()
    {
        $platform = new MySQLPlatform();
        $platform->buildConnection($this->getMock(ConnectionSettings::class));
    }

    public function test_returns_statement_builder()
    {
        $platform = new MySQLPlatform();
        $builder = $platform->getStatementBuilder();
        $this->assertInstanceOf(DefaultStatementBuilder::class, $builder);
    }

    public function test_returns_definition_provider()
    {
        $platform = new MySQLPlatform();
        $provider = $platform->getDefinitionProvider($this->getMock(Connection::class));
        $this->assertInstanceOf(DefinitionProvider::class, $provider);
    }
}
