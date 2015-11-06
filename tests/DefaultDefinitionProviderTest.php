<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\Connection;
use BinSoul\Db\Definition\Schema;
use BinSoul\Db\Definition\Table;
use BinSoul\Db\MySQL\DefaultDefinitionProvider;
use BinSoul\Db\Result;

class DefaultDefinitionProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test_lists_schemas()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
           ['SCHEMA_NAME' => 'foo'],
           ['SCHEMA_NAME' => 'bar'],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $schemas = $provider->listSchemas();
        $this->assertCount(2, $schemas);
        $this->assertEquals('foo', $schemas[0]->getName());
        $this->assertEquals('bar', $schemas[1]->getName());
    }

    public function test_lists_tables()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
           ['TABLE_NAME' => 'foo', 'TABLE_TYPE' => 'BASE TABLE'],
           ['TABLE_NAME' => 'bar', 'TABLE_TYPE' => 'VIEW'],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $tables = $provider->listTables();
        $this->assertCount(1, $tables);
        $this->assertEquals('foo', $tables[0]->getName());

        $tables = $provider->listTables(new Schema('qux'));
        $this->assertCount(1, $tables);
        $this->assertEquals('foo', $tables[0]->getName());
    }

    public function test_lists_columns()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
            [
                'COLUMN_NAME' => 'id',
                'COLUMN_TYPE' => 'int(11)',
                'COLUMN_DEFAULT' => null,
                'IS_NULLABLE' => 'NO',
                'COLUMN_COMMENT' => '',
                'EXTRA' => 'auto_increment',
            ],
            [
                'COLUMN_NAME' => 'name',
                'COLUMN_TYPE' => 'varchar(128)',
                'COLUMN_DEFAULT' => 'foobar',
                'IS_NULLABLE' => 'NO',
                'COLUMN_COMMENT' => 'Name',
                'EXTRA' => '',
            ],
            [
                'COLUMN_NAME' => 'value',
                'COLUMN_TYPE' => 'decimal(14,8)',
                'COLUMN_DEFAULT' => null,
                'COLUMN_COMMENT' => '',
                'IS_NULLABLE' => 'YES',
                'EXTRA' => '',
            ],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $columns = $provider->listColumns($table);
        $this->assertCount(3, $columns);
        $this->assertEquals('id', $columns[0]->getName());
        $this->assertEquals('name', $columns[1]->getName());
        $this->assertEquals('value', $columns[2]->getName());

        $columns = $provider->listColumns($table, new Schema('qux'));
        $this->assertCount(3, $columns);
        $this->assertEquals('id', $columns[0]->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function test_throws_exception_for_invalid_column()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
            [
                'COLUMN_NAME' => 'id',
                'COLUMN_TYPE' => 'foobar',
                'COLUMN_DEFAULT' => null,
                'IS_NULLABLE' => 'NO',
                'COLUMN_COMMENT' => '',
                'EXTRA' => 'auto_increment',
            ],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $provider->listColumns($table);
    }

    public function test_returns_primary_key()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
            [
                'INDEX_NAME' => 'PRIMARY',
                'CONSTRAINT_TYPE' => 'PRIMARY KEY',
                'COLUMN_NAME' => 'id',
            ],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $this->assertEquals(['id'], $provider->getPrimaryKey($table)->getColumns());
        $this->assertEquals(['id'], $provider->getPrimaryKey($table, new Schema('qux'))->getColumns());
    }

    public function test_returns_null_if_no_primary_key()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $this->assertNull($provider->getPrimaryKey($table));
    }

    public function test_lists_indexes()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
            [
                'INDEX_NAME' => 'PRIMARY',
                'CONSTRAINT_TYPE' => 'PRIMARY KEY',
                'COLUMN_NAME' => 'id',
            ],
            [
                'INDEX_NAME' => 'idx_foo',
                'CONSTRAINT_TYPE' => 'UNIQUE',
                'COLUMN_NAME' => 'foo',
            ],
            [
                'INDEX_NAME' => 'idx_bar',
                'CONSTRAINT_TYPE' => null,
                'COLUMN_NAME' => 'bar',
            ],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $indexes = $provider->listIndexes($table);

        $this->assertCount(2, $indexes);
        $this->assertEquals('idx_foo', $indexes[0]->getName());
        $this->assertEquals('idx_bar', $indexes[1]->getName());
    }

    public function test_lists_foreign_keys()
    {
        $result = $this->getMock(Result::class);
        $result->method('all')->willReturn([
            [
                'CONSTRAINT_NAME' => 'fk_foo',
                'REFERENCED_TABLE_NAME' => 'foo',
                'REFERENCED_COLUMN_NAME' => 'id',
                'COLUMN_NAME' => 'foo_id',
                'UPDATE_RULE' => 'CASCADE',
                'DELETE_RULE' => 'SET NULL',
            ],
            [
                'CONSTRAINT_NAME' => 'fk_bar',
                'REFERENCED_TABLE_NAME' => 'bar',
                'REFERENCED_COLUMN_NAME' => 'id',
                'COLUMN_NAME' => 'bar_id',
                'UPDATE_RULE' => 'CASCADE',
                'DELETE_RULE' => 'RESTRICT',
            ],
        ]);

        $connection = $this->getMock(Connection::class);
        $connection->method('execute')->willReturn($result);

        $provider = new DefaultDefinitionProvider($connection);
        $table = new Table('foo');
        $keys = $provider->listForeignKeys($table);

        $this->assertCount(2, $keys);
        $this->assertEquals('fk_foo', $keys[0]->getName());
        $this->assertEquals('fk_bar', $keys[1]->getName());

        $keys = $provider->listForeignKeys($table, new Schema('qux'));
        $this->assertCount(2, $keys);
    }
}
