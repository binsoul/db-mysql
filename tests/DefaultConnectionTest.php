<?php

namespace BinSoul\Test\Db\Platform\MySQL;

use BinSoul\Db\Platform\MySQL\DefaultConnection;
use BinSoul\Db\Platform\MySQL\MySQLConnectionSettings;
use BinSoul\Db\Platform\MySQL\MySQLiFake;
use BinSoul\Db\Platform\MySQL\MySQLiStmtFake;
use BinSoul\Db\Platform\MySQL\AbstractResult;

include_once 'MySQLiFake.php';

class DefaultConnectionTest extends \PHPUnit_Framework_TestCase
{
    private function buildSettings()
    {
        return MySQLConnectionSettings::fromArray(
            [
                'host' => '127.0.0.1',
                'username' => 'root',
                'password' => 'password',
                'schema' => 'schema',
            ]
        );
    }
    protected function setUp()
    {
        MySQLiFake::reset();
    }

    public function test_open_succes()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $this->assertTrue($connection->open());
        $this->assertFalse($connection->open());
    }

    /**
     * @expectedException \BinSoul\Db\Exception\ConnectionException
     */
    public function test_open_failure()
    {
        MySQLiFake::$allowConnect = false;

        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
    }

    public function test_close_success()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertTrue($connection->close());
        $this->assertTrue(MySQLiFake::$closeCalled);

        $this->assertFalse($connection->close());
    }

    public function test_close_rolls_back_transactions()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $connection->begin();
        $this->assertTrue($connection->close());
        $this->assertTrue(MySQLiFake::$rollbackCalled);
    }

    public function test_begin_starts_transaction()
    {
        MySQLiFake::$allowAutocommit = true;
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertTrue($connection->begin());
        $this->assertTrue(MySQLiFake::$autocommitCalled);
        $this->assertTrue(MySQLiFake::$queryCalled);
        $this->assertContains('start transaction', MySQLiFake::$lastStatement, '', true);
    }

    public function test_begin_returns_false_if_autocommit_fails()
    {
        MySQLiFake::$allowAutocommit = false;
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertFalse($connection->begin());
        $this->assertTrue(MySQLiFake::$autocommitCalled);
        $this->assertFalse(MySQLiFake::$queryCalled);
    }

    public function test_begin_uses_savepoints()
    {
        MySQLiFake::$allowAutocommit = true;
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertTrue($connection->begin());
        $this->assertTrue($connection->begin());
        $this->assertContains('savepoint', MySQLiFake::$lastStatement, '', true);
    }

    public function test_commit()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $connection->begin();
        $this->assertTrue($connection->commit());
        $this->assertTrue(MySQLiFake::$commitCalled);
        $this->assertTrue(MySQLiFake::$autocommitCalled);
    }

    public function test_commit_uses_savepoints()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $connection->begin();
        $connection->begin();
        $this->assertTrue($connection->commit());
        $this->assertContains('savepoint', MySQLiFake::$lastStatement, '', true);
    }

    public function test_commit_returns_false_if_not_in_transaction()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertFalse($connection->commit());
        $this->assertFalse(MySQLiFake::$commitCalled);
    }

    public function test_rollback()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $connection->begin();
        $this->assertTrue($connection->rollback());
        $this->assertTrue(MySQLiFake::$rollbackCalled);
        $this->assertTrue(MySQLiFake::$autocommitCalled);
    }

    public function test_rollback_uses_savepoints()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $connection->begin();
        $connection->begin();
        $this->assertTrue($connection->rollback());
        $this->assertContains('rollback', MySQLiFake::$lastStatement, '', true);
    }

    public function test_rollback_returns_false_if_not_in_transaction()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();
        $this->assertFalse($connection->rollback());
        $this->assertFalse(MySQLiFake::$rollbackCalled);
    }

    public function test_execute_without_parameters()
    {
        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();

        $result = $connection->execute('select * from table', []);
        $this->assertTrue(MySQLiFake::$queryCalled);
        $this->assertInstanceOf(AbstractResult::class, $result);
    }

    /**
     * @expectedException \BinSoul\Db\Exception\OperationException
     */
    public function test_execute_without_parameters_throws_operation_exception()
    {
        MySQLiFake::$allowQuery = false;

        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();

        $connection->execute('select * from table', []);
    }

    /**
     * @expectedException \BinSoul\Db\Exception\ConnectionException
     */
    public function test_execute_without_parameters_throws_connection_exception()
    {
        MySQLiFake::$allowQuery = false;
        MySQLiFake::$definedErrorCode = 2006;

        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();

        $connection->execute('select * from table', []);
    }

    public function test_execute_with_parameters()
    {
        $connection = new DefaultConnection($this->buildSettings(), false);
        $connection->open();

        $result = $connection->execute('select * from table where id=?', [1, 1.5, 'foo', [], [1, 2], null]);
        $this->assertTrue(MySQLiFake::$prepareCalled);
        $this->assertInstanceOf(AbstractResult::class, $result);

        MySQLiFake::reset();

        $connection = new DefaultConnection($this->buildSettings(), true);
        $connection->open();

        $result = $connection->execute('select * from table where id=?', [1, 1.5, 'foo', [], [1, 2], null]);
        $this->assertTrue(MySQLiFake::$prepareCalled);
        $this->assertInstanceOf(AbstractResult::class, $result);
    }

    /**
     * @expectedException \BinSoul\Db\Exception\ConnectionException
     */
    public function test_prepare_throws_exception()
    {
        MySQLiFake::$allowPrepare = false;
        MySQLiFake::$definedErrorCode = 2006;

        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();

        $connection->execute('select * from table where id=?', [1]);
    }

    /**
     * @expectedException \BinSoul\Db\Exception\ConnectionException
     */
    public function test_execute_throws_exception()
    {
        MySQLiStmtFake::$allowExecute = false;
        MySQLiStmtFake::$definedErrorCode = 2006;

        $connection = new DefaultConnection($this->buildSettings());
        $connection->open();

        $connection->execute('select * from table where id=?', [1]);
    }
}
