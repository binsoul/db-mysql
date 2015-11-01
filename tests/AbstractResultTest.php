<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\MySQL\MySQLiFake;
use BinSoul\Db\MySQL\MySQLiStmtFake;
use BinSoul\Db\MySQL\StatementResult;
use BinSoul\Db\Result;

include_once 'MySQLiFake.php';

abstract class AbstractResultTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        MySQLiStmtFake::reset();
    }

    /**
     * @param bool $withResource
     * @param bool $withMetadata
     *
     * @return Result
     */
    abstract protected function buildResult($withResource = true, $withMetadata = true);

    public function test_get_all_rows()
    {
        $result = $this->buildResult();
        $this->assertEquals([MySQLiStmtFake::$row], $result->all());
    }

    public function test_get_all_rows_no_fields()
    {
        MySQLiStmtFake::$allowFields = false;
        $result = $this->buildResult();
        $this->assertEquals([], $result->all());
    }

    public function test_get_all_rows_no_rows()
    {
        MySQLiStmtFake::$allowRow = false;
        $result = $this->buildResult();
        $this->assertEquals([], $result->all());
    }

    public function test_get_all_rows_null_resource()
    {
        MySQLiStmtFake::$allowRow = false;
        $result = $this->buildResult(false);
        $this->assertEquals([], $result->all());
    }

    /**
     * @expectedException \LogicException
     */
    public function test_get_all_rows_throws_exception_for_double_fetch()
    {
        MySQLiStmtFake::$allowRow = false;
        $result = $this->buildResult(false);
        $result->all();
        $result->all();
    }

    public function test_get_single_row()
    {
        $result = $this->buildResult();
        $this->assertEquals(MySQLiStmtFake::$row, $result->row());
    }

    public function test_get_single_row_no_rows()
    {
        MySQLiStmtFake::$allowRow = false;
        $result = $this->buildResult();
        $this->assertNull($result->row());
    }

    public function test_get_column()
    {
        $result = $this->buildResult();
        $this->assertEquals([MySQLiStmtFake::$row['column1']], $result->column('column1'));
    }

    public function test_get_cell()
    {
        $result = $this->buildResult();
        $this->assertEquals(MySQLiStmtFake::$row['column1'], $result->cell('column1'));
    }

    public function test_get_cell_no_rows()
    {
        MySQLiStmtFake::$allowRow = false;
        $result = $this->buildResult();
        $this->assertNull($result->cell('column1'));
    }

    public function test_autoincrement_id()
    {
        $mysqli = new MySQLiFake();
        $mysqli->insert_id = 123;
        $stmt = new MySQLiStmtFake();
        $stmt->insert_id = 456;

        $result = new StatementResult($stmt, $mysqli);
        $this->assertEquals(123, $result->autoIncrementID());

        $result = new StatementResult($mysqli, $stmt);
        $this->assertEquals(456, $result->autoIncrementID());
    }

    public function test_affected_rows()
    {
        $mysqli = new MySQLiFake();
        $mysqli->affected_rows = 123;
        $stmt = new MySQLiStmtFake();
        $stmt->affected_rows = 456;

        $result = new StatementResult($stmt, $mysqli);
        $this->assertEquals(123, $result->affectedRows());

        $result = new StatementResult($mysqli, $stmt);
        $this->assertEquals(456, $result->affectedRows());
    }
}
