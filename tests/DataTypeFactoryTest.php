<?php

namespace BinSoul\Test\Db\MySQL;

use BinSoul\Db\Definition\DataType\BigIntegerType;
use BinSoul\Db\Definition\DataType\BitType;
use BinSoul\Db\Definition\DataType\BooleanType;
use BinSoul\Db\Definition\DataType\CharType;
use BinSoul\Db\Definition\DataType\DatetimeType;
use BinSoul\Db\Definition\DataType\DateType;
use BinSoul\Db\Definition\DataType\DecimalType;
use BinSoul\Db\Definition\DataType\DoubleType;
use BinSoul\Db\Definition\DataType\FloatType;
use BinSoul\Db\Definition\DataType\IntegerType;
use BinSoul\Db\Definition\DataType\MediumIntegerType;
use BinSoul\Db\Definition\DataType\SmallIntegerType;
use BinSoul\Db\Definition\DataType\TimeType;
use BinSoul\Db\Definition\DataType\TinyIntegerType;
use BinSoul\Db\Definition\DataType\UnixTimestampType;
use BinSoul\Db\Definition\DataType\VarcharType;
use BinSoul\Db\MySQL\DataTypeFactory;

class DataTypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function types()
    {
        return [
          ['int(11)', IntegerType::class],
          ['varchar(128)', VarcharType::class],
          ['char(128)', CharType::class],
          ['decimal(14, 8)', DecimalType::class],
          ['datetime', DatetimeType::class],
          ['date', DateType::class],
          ['time', TimeType::class],
          ['timestamp', UnixTimestampType::class],
          ['float', FloatType::class],
          ['double', DoubleType::class],
          ['bigint', BigIntegerType::class],
          ['smallint', SmallIntegerType::class],
          ['tinyint(2)', TinyIntegerType::class],
          ['tinyint(1)', BooleanType::class],
          ['mediumint', MediumIntegerType::class],
          ['bit(32)', BitType::class],
        ];
    }
    /**
     * @param $string
     * @param $class
     *
     * @dataProvider types
     */
    public function test_parses_types($string, $class)
    {
        $factory = new DataTypeFactory();
        $this->assertInstanceOf($class, $factory->parseSQLType($string));
    }

    public function test_returns_null_for_invalid_type()
    {
        $factory = new DataTypeFactory();
        $this->assertNull($factory->parseSQLType('foobar'));
        $this->assertNull($factory->parseSQLType('foo(1) bar'));
    }
}
