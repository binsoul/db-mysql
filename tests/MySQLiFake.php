<?php

namespace BinSoul\Db\MySQL;

class MySQLiResultFake
{
    public static $allowFields = true;
    public static $allowRow = true;

    public static $row = [
        'column1' => 'foobar',
        'column2' => 1.5,
        'column3' => 123456,
        'column4' => 1.23456,
        'column5' => null,
        'column6' => '2015-11-01 12:34:56',
    ];

    public static function reset()
    {
        self::$allowFields = true;
        self::$allowRow = true;
    }

    public function fetch_fields()
    {
        if (!self::$allowFields) {
            return [];
        }

        $column1 = new \stdClass();
        $column1->name = 'column1';
        $column1->type = MYSQLI_TYPE_VAR_STRING;

        $column2 = new \stdClass();
        $column2->name = 'column2';
        $column2->type = MYSQLI_TYPE_FLOAT;

        $column3 = new \stdClass();
        $column3->name = 'column3';
        $column3->type = MYSQLI_TYPE_LONG;

        $column4 = new \stdClass();
        $column4->name = 'column4';
        $column4->type = MYSQLI_TYPE_DECIMAL;

        $column5 = new \stdClass();
        $column5->name = 'column5';
        $column5->type = MYSQLI_TYPE_DECIMAL;

        $column6 = new \stdClass();
        $column6->name = 'column6';
        $column6->type = MYSQLI_TYPE_DATETIME;

        return [
            $column1,
            $column2,
            $column3,
            $column4,
            $column5,
            $column6,
        ];
    }

    public function fetch_all()
    {
        if (self::$allowRow) {
            return [self::$row];
        }

        return;
    }

    public function free()
    {
    }
}

class MySQLiStmtFake extends MySQLiResultFake
{
    private $rowCount = 0;

    public $error = '';
    public $errno = 0;

    public $affected_rows = 0;
    public $insert_id = 1;

    public static $definedErrorCode = 0;
    public static $allowExecute = true;
    public static $executeCalled = false;

    public function bind_param()
    {
    }

    public function execute()
    {
        self::$executeCalled = true;

        $this->errno = self::$definedErrorCode;

        return self::$allowExecute;
    }

    public function get_result()
    {
        return $this;
    }

    public function result_metadata()
    {
        return $this;
    }

    public function bind_result(&$var1, &$var2, &$var3, &$var4, &$var5, &$var6)
    {
        $this->rowCount = 0;
        $var1 = 'foobar';
        $var2 = 1.5;
        $var3 = 123456;
        $var4 = '1.23456000';
        $var5 = null;
        $var6 = '2015-11-01 12:34:56';
    }

    public function fetch()
    {
        if (!self::$allowRow) {
            return false;
        }

        ++$this->rowCount;
        if ($this->rowCount > 1) {
            return false;
        }

        return true;
    }

    public function free_result()
    {
    }
}

class MySQLiFake
{
    public $error = '';
    public $errno = 0;

    public $affected_rows = 0;
    public $insert_id = 1;

    public static $definedErrorCode = 0;
    public static $allowConnect = true;
    public static $closeCalled = false;
    public static $allowAutocommit = true;
    public static $autocommitCalled = false;
    public static $rollbackCalled = false;
    public static $commitCalled = false;
    public static $allowQuery = true;
    public static $queryCalled = false;
    public static $lastStatement = '';
    public static $allowPrepare = true;
    public static $prepareCalled = false;

    public static function reset()
    {
        self::$allowConnect = true;
        self::$closeCalled = false;
        self::$allowAutocommit = true;
        self::$commitCalled = false;
        self::$rollbackCalled = false;
        self::$allowQuery = true;
        self::$queryCalled = false;
        self::$lastStatement = '';
        self::$allowPrepare = true;
        self::$prepareCalled = false;
    }

    public function options()
    {
    }

    public function set_charset()
    {
    }

    public function real_connect()
    {
        $this->errno = self::$definedErrorCode;

        return self::$allowConnect;
    }

    public function close()
    {
        self::$closeCalled = true;
    }

    public function autocommit()
    {
        self::$autocommitCalled = true;

        $this->errno = self::$definedErrorCode;

        return self::$allowAutocommit;
    }

    public function commit()
    {
        self::$commitCalled = true;

        return true;
    }

    public function rollback()
    {
        self::$rollbackCalled = true;

        return true;
    }

    public function query($statement)
    {
        self::$queryCalled = true;

        $this->errno = self::$definedErrorCode;
        self::$lastStatement = $statement;

        return self::$allowQuery ? (stripos($statement, 'select') !== false ? new MySQLiResultFake() : true) : false;
    }

    public function prepare()
    {
        self::$prepareCalled = true;

        $this->errno = self::$definedErrorCode;

        return self::$allowPrepare ? new MySQLiStmtFake() : false;
    }
}

function mysqli_init()
{
    return new MySQLiFake();
}

function mysqli_connect_error()
{
    return 'error';
}

function mysqli_connect_errno()
{
    return 12345;
}
