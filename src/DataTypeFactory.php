<?php

namespace BinSoul\Db\MySQL;

use BinSoul\Db\Definition\DataType;
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

/**
 * Provides methods to convert between MySQL data types and internal data types.
 */
class DataTypeFactory
{
    /**
     * @param string $type
     *
     * @return DataType|null
     */
    public function parseSQLType($type)
    {
        $targetType = strtolower($type);

        if (!preg_match('/^([a-z]+)\(?([0-9]+)?,?\s*([0-9]+)?\)?\s*(unsigned)?\s*(zerofill)?$/', $type, $matches)) {
            return;
        }

        if ($targetType == 'tinyint(1)') {
            return new BooleanType();
        }

        switch ($matches[1]) {
            case 'int':
                return new IntegerType();
            case 'varchar':
                return new VarcharType($matches[2]);
            case 'char':
                return new CharType($matches[2]);
            case 'decimal':
                return new DecimalType($matches[2], $matches[3]);
            case 'datetime':
                return new DatetimeType();
            case 'date':
                return new DateType();
            case 'time':
                return new TimeType();
            case 'timestamp':
                return new UnixTimestampType();
            case 'float':
                return new FloatType();
            case 'double':
                return new DoubleType();
            case 'bigint':
                return new BigIntegerType();
            case 'smallint':
                return new SmallIntegerType();
            case 'tinyint':
                return new TinyIntegerType();
            case 'mediumint':
                return new MediumIntegerType();
            case 'bit':
                return new BitType($matches[2]);

            default:
                return;
        }
    }
}
